<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use NiekNijland\Ista\Exception\IstaException;
use NiekNijland\Ista\Support\Authenticator;
use NiekNijland\Ista\Support\CacheStore;
use NiekNijland\Ista\Support\JwtToken;
use NiekNijland\Ista\Tests\ArrayCache;
use PHPUnit\Framework\TestCase;

class AuthenticatorTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__.'/../Fixtures/'.$name);
    }

    private function createAuthenticator(MockHandler $mock, ?ArrayCache $cache = null): Authenticator
    {
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        $cacheStore = new CacheStore($cache, 3600);

        return new Authenticator($httpClient, $cacheStore, 'test@example.com', 'password123');
    }

    /**
     * @return list<Response>
     */
    private function successfulLoginResponses(): array
    {
        return [
            new Response(200, [], $this->fixture('keycloak-login-form.html')),
            new Response(200, [], $this->fixture('oidc-callback.html')),
            new Response(200, [], $this->fixture('login-response.html')),
        ];
    }

    public function test_successful_login_extracts_jwt(): void
    {
        $mock = new MockHandler($this->successfulLoginResponses());

        $authenticator = $this->createAuthenticator($mock);
        $token = $authenticator->authenticate();

        $this->assertInstanceOf(JwtToken::class, $token);
        $this->assertNotEmpty($token->token);
        $this->assertStringStartsWith('eyJ', $token->token);
    }

    public function test_missing_keycloak_form_throws_exception(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '<html><body><h1>No form here</h1></body></html>'),
        ]);

        $authenticator = $this->createAuthenticator($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('Login form not found');

        $authenticator->authenticate();
    }

    public function test_missing_oidc_callback_fields_throws_exception(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('keycloak-login-form.html')),
            new Response(200, [], '<html><body>No OIDC fields</body></html>'),
        ]);

        $authenticator = $this->createAuthenticator($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('OIDC callback fields not found');

        $authenticator->authenticate();
    }

    public function test_missing_jwt_field_throws_exception(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('keycloak-login-form.html')),
            new Response(200, [], $this->fixture('oidc-callback.html')),
            new Response(200, [], '<html><body><h1>No JWT here</h1></body></html>'),
        ]);

        $authenticator = $this->createAuthenticator($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('JWT field not found');

        $authenticator->authenticate();
    }

    public function test_empty_jwt_value_throws_exception(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('keycloak-login-form.html')),
            new Response(200, [], $this->fixture('oidc-callback.html')),
            new Response(200, [], '<html><body><input id="__twj_" value="" /></body></html>'),
        ]);

        $authenticator = $this->createAuthenticator($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('token value is empty');

        $authenticator->authenticate();
    }

    public function test_network_error_throws_exception(): void
    {
        $mock = new MockHandler([
            new ConnectException(
                'Connection refused',
                new Request('GET', 'https://mijn.ista.nl/home/index'),
            ),
        ]);

        $authenticator = $this->createAuthenticator($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('Authentication request failed');

        $authenticator->authenticate();
    }

    public function test_keycloak_network_error_throws_exception(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('keycloak-login-form.html')),
            new ConnectException(
                'Connection refused',
                new Request('POST', 'https://login.ista.com'),
            ),
        ]);

        $authenticator = $this->createAuthenticator($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('Authentication request failed');

        $authenticator->authenticate();
    }

    public function test_oidc_callback_network_error_throws_exception(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('keycloak-login-form.html')),
            new Response(200, [], $this->fixture('oidc-callback.html')),
            new ConnectException(
                'Connection refused',
                new Request('POST', 'https://mijn.ista.nl/signin-oidc'),
            ),
        ]);

        $authenticator = $this->createAuthenticator($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('Authentication request failed');

        $authenticator->authenticate();
    }

    public function test_cached_token_avoids_reauthentication(): void
    {
        $cache = new ArrayCache;

        // First call: full OIDC login and cache the token
        $mock = new MockHandler($this->successfulLoginResponses());

        $authenticator = $this->createAuthenticator($mock, $cache);
        $firstToken = $authenticator->authenticate();

        // Second call: should use cached token (no HTTP call needed)
        $emptyMock = new MockHandler;
        $secondAuthenticator = $this->createAuthenticator($emptyMock, $cache);
        $secondToken = $secondAuthenticator->authenticate();

        $this->assertSame($firstToken->token, $secondToken->token);
    }
}
