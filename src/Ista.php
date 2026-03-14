<?php

declare(strict_types=1);

namespace NiekNijland\Ista;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use NiekNijland\Ista\Data\ConsumptionAverageResult;
use NiekNijland\Ista\Data\ConsumptionValuesResult;
use NiekNijland\Ista\Data\MonthlyConsumptionResult;
use NiekNijland\Ista\Data\UserValuesResult;
use NiekNijland\Ista\Exception\IstaException;
use NiekNijland\Ista\Support\Authenticator;
use NiekNijland\Ista\Support\CacheStore;
use NiekNijland\Ista\Support\JwtToken;
use Psr\SimpleCache\CacheInterface;
use SensitiveParameter;

class Ista implements IstaInterface
{
    private const string USER_VALUES_URL = 'https://mijn.ista.nl/api/Values/UserValues';

    private const string CONSUMPTION_AVERAGES_URL = 'https://mijn.ista.nl/api/Values/ConsumptionAverages';

    private const string CONSUMPTION_VALUES_URL = 'https://mijn.ista.nl/api/Values/ConsumptionValues';

    private const string MONTH_VALUES_URL = 'https://mijn.ista.nl/api/Consumption/MonthValues';

    private const string JWT_REFRESH_URL = 'https://mijn.ista.nl/api/Authorization/JWTRefresh';

    private const int MONTH_VALUES_MAX_ATTEMPTS = 10;

    private const int MONTH_VALUES_RETRY_DELAY_SECONDS = 2;

    private readonly Authenticator $authenticator;

    private readonly CacheStore $cacheStore;

    private readonly ClientInterface $httpClient;

    private ?JwtToken $token = null;

    public function __construct(
        private readonly string $username,
        #[SensitiveParameter]
        private readonly string $password,
        ?ClientInterface $httpClient = null,
        ?CacheInterface $cache = null,
        int $cacheTtl = 3600,
    ) {
        $this->httpClient = $httpClient ?? new Client;
        $this->cacheStore = new CacheStore($cache, $cacheTtl);
        $this->authenticator = new Authenticator(
            $this->httpClient,
            $this->cacheStore,
            $this->username,
            $this->password,
        );
    }

    public function getUserValues(): UserValuesResult
    {
        $cached = $this->cacheStore->fetch(
            'ista:user-values',
            static fn (array $data): UserValuesResult => UserValuesResult::fromArray($data),
        );

        if ($cached instanceof UserValuesResult) {
            return $cached;
        }

        $token = $this->getToken();

        $body = $this->postJson(self::USER_VALUES_URL, [
            'JWT' => $token->toString(),
        ]);

        $result = UserValuesResult::fromArray($body);

        $this->cacheStore->store('ista:user-values', $result->toArray());

        return $result;
    }

    public function getConsumptionAverages(string $cuid, DateTimeImmutable $start, DateTimeImmutable $end): ConsumptionAverageResult
    {
        $cacheKey = 'ista:consumption-averages:'.$cuid.':'.$start->format('Y-m-d').':'.$end->format('Y-m-d');

        $cached = $this->cacheStore->fetch(
            $cacheKey,
            static fn (array $data): ConsumptionAverageResult => ConsumptionAverageResult::fromArray($data),
        );

        if ($cached instanceof ConsumptionAverageResult) {
            return $cached;
        }

        $token = $this->getToken();

        $body = $this->postJson(self::CONSUMPTION_AVERAGES_URL, [
            'JWT' => $token->toString(),
            'Cuid' => $cuid,
            'PAR' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
        ]);

        $result = ConsumptionAverageResult::fromArray($body);

        $this->cacheStore->store($cacheKey, $result->toArray());

        return $result;
    }

    public function getMonthlyConsumption(string $cuid): MonthlyConsumptionResult
    {
        $cacheKey = 'ista:monthly-consumption:'.$cuid;

        $cached = $this->cacheStore->fetch(
            $cacheKey,
            static fn (array $data): MonthlyConsumptionResult => MonthlyConsumptionResult::fromArray($data),
        );

        if ($cached instanceof MonthlyConsumptionResult) {
            return $cached;
        }

        $token = $this->getToken();

        $body = $this->postJson(self::MONTH_VALUES_URL, [
            'JWT' => $token->toString(),
            'Cuid' => $cuid,
        ]);

        $result = MonthlyConsumptionResult::fromArray($body);

        $attempts = 1;

        while ($result->hasMonths < $result->showMonths && $attempts < self::MONTH_VALUES_MAX_ATTEMPTS) {
            sleep(self::MONTH_VALUES_RETRY_DELAY_SECONDS);

            $body = $this->postJson(self::MONTH_VALUES_URL, [
                'JWT' => $token->toString(),
                'Cuid' => $cuid,
            ]);

            $result = MonthlyConsumptionResult::fromArray($body);
            $attempts++;
        }

        $this->cacheStore->store($cacheKey, $result->toArray());

        return $result;
    }

    public function getConsumptionValues(string $cuid, int $year, string $start, string $end): ConsumptionValuesResult
    {
        $cacheKey = 'ista:consumption-values:'.$cuid.':'.$year.':'.$start.':'.$end;

        $cached = $this->cacheStore->fetch(
            $cacheKey,
            static fn (array $data): ConsumptionValuesResult => ConsumptionValuesResult::fromArray($data),
        );

        if ($cached instanceof ConsumptionValuesResult) {
            return $cached;
        }

        $token = $this->getToken();

        $body = $this->postJson(self::CONSUMPTION_VALUES_URL, [
            'JWT' => $token->toString(),
            'Cuid' => $cuid,
            'Billingperiod' => [
                'y' => $year,
                's' => $start,
                'e' => $end,
            ],
        ]);

        $result = ConsumptionValuesResult::fromArray($body);

        $this->cacheStore->store($cacheKey, $result->toArray());

        return $result;
    }

    private function getToken(): JwtToken
    {
        if ($this->token instanceof JwtToken && ! $this->token->isExpired()) {
            return $this->token;
        }

        if ($this->token instanceof JwtToken) {
            $refreshed = $this->refreshToken($this->token);

            if ($refreshed instanceof JwtToken) {
                $this->token = $refreshed;

                return $this->token;
            }
        }

        $this->token = $this->authenticator->authenticate();

        return $this->token;
    }

    private function refreshToken(JwtToken $expiredToken): ?JwtToken
    {
        try {
            $body = $this->postJson(self::JWT_REFRESH_URL, [
                'JWT' => $expiredToken->toString(),
            ]);

            $jwt = $body['JWT'] ?? null;

            if (is_string($jwt) && $jwt !== '') {
                $newToken = new JwtToken($jwt);

                if (! $newToken->isExpired()) {
                    $this->cacheStore->store('ista:jwt', ['token' => $newToken->token]);

                    return $newToken;
                }
            }
        } catch (IstaException) {
            // Refresh failed, caller will fall back to full authentication
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function postJson(string $url, array $payload): array
    {
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'authority' => 'mijn.ista.nl',
                    'accept' => 'application/json, text/javascript, */*; q=0.01',
                    'content-type' => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (GuzzleException $e) {
            throw new IstaException('API request failed: '.$e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() === 204) {
            return [];
        }

        $contents = $response->getBody()->getContents();

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new IstaException('Failed to decode API response: '.$e->getMessage(), 0, $e);
        }

        if (! is_array($data)) {
            throw new IstaException('API did not return a valid response.');
        }

        /** @var array<string, mixed> $data */
        return $data;
    }
}
