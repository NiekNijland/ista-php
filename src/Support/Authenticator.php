<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Support;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use NiekNijland\Ista\Exception\IstaException;
use SensitiveParameter;

class Authenticator
{
    private const string HOME_URL = 'https://mijn.ista.nl/home/index';

    private const string SIGNIN_OIDC_URL = 'https://mijn.ista.nl/signin-oidc';

    private const string JWT_FIELD_ID = '__twj_';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly CacheStore $cacheStore,
        private readonly string $username,
        #[SensitiveParameter]
        private readonly string $password,
    ) {}

    public function authenticate(): JwtToken
    {
        $cached = $this->cacheStore->fetch(
            'ista:jwt',
            static fn (array $data): JwtToken => new JwtToken((string) ($data['token'] ?? '')),
        );

        if ($cached instanceof JwtToken && ! $cached->isExpired()) {
            return $cached;
        }

        return $this->login();
    }

    private function login(): JwtToken
    {
        $jar = new CookieJar;

        $loginFormHtml = $this->getKeycloakLoginForm($jar);
        $formPostHtml = $this->submitCredentials($jar, $loginFormHtml);
        $homeHtml = $this->submitOidcCallback($jar, $formPostHtml);

        $token = $this->extractJwtFromHtml($homeHtml);

        $this->cacheStore->store('ista:jwt', ['token' => $token->token]);

        return $token;
    }

    private function getKeycloakLoginForm(CookieJar $jar): string
    {
        try {
            $response = $this->httpClient->request('GET', self::HOME_URL, [
                'cookies' => $jar,
            ]);
        } catch (GuzzleException $e) {
            throw new IstaException('Authentication request failed: '.$e->getMessage(), 0, $e);
        }

        return $response->getBody()->getContents();
    }

    private function submitCredentials(CookieJar $jar, string $loginFormHtml): string
    {
        $action = $this->extractFormAction($loginFormHtml);

        try {
            $response = $this->httpClient->request('POST', $action, [
                'cookies' => $jar,
                'form_params' => [
                    'username' => $this->username,
                    'password' => $this->password,
                    'credentialId' => '',
                ],
                'allow_redirects' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new IstaException('Authentication request failed: '.$e->getMessage(), 0, $e);
        }

        return $response->getBody()->getContents();
    }

    private function submitOidcCallback(CookieJar $jar, string $formPostHtml): string
    {
        $fields = $this->extractOidcCallbackFields($formPostHtml);

        try {
            $response = $this->httpClient->request('POST', self::SIGNIN_OIDC_URL, [
                'cookies' => $jar,
                'form_params' => $fields,
            ]);
        } catch (GuzzleException $e) {
            throw new IstaException('Authentication request failed: '.$e->getMessage(), 0, $e);
        }

        return $response->getBody()->getContents();
    }

    private function extractFormAction(string $html): string
    {
        $pattern = '/action="([^"]*)"/';

        if (preg_match($pattern, $html, $matches) !== 1) {
            throw new IstaException(
                'Login form not found in response. The authentication flow of mijn.ista.nl may have changed.',
            );
        }

        return html_entity_decode($matches[1]);
    }

    /**
     * @return array<string, string>
     */
    private function extractOidcCallbackFields(string $html): array
    {
        $fields = [];
        $pattern = '/NAME="([^"]*)" VALUE="([^"]*)"/';

        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER) === 0) {
            throw new IstaException(
                'OIDC callback fields not found in response. The authentication flow of mijn.ista.nl may have changed.',
            );
        }

        foreach ($matches as $match) {
            $fields[$match[1]] = $match[2];
        }

        return $fields;
    }

    private function extractJwtFromHtml(string $html): JwtToken
    {
        $pattern = '/id="'.preg_quote(self::JWT_FIELD_ID, '/').'"[^>]*value="([^"]*)"/';

        if (preg_match($pattern, $html, $matches) !== 1) {
            throw new IstaException(
                'JWT field not found in login response. The HTML structure of mijn.ista.nl may have changed.',
            );
        }

        $tokenValue = $matches[1];

        if ($tokenValue === '') {
            throw new IstaException('JWT field was found but the token value is empty.');
        }

        return new JwtToken($tokenValue);
    }
}
