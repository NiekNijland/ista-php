<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use NiekNijland\Ista\Data\ConsumptionAverageResult;
use NiekNijland\Ista\Data\ConsumptionValuesResult;
use NiekNijland\Ista\Data\MonthlyConsumptionResult;
use NiekNijland\Ista\Data\UserValuesResult;
use NiekNijland\Ista\Exception\IstaException;
use NiekNijland\Ista\Ista;
use NiekNijland\Ista\Tests\ArrayCache;
use PHPUnit\Framework\TestCase;

class IstaTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__.'/../Fixtures/'.$name);
    }

    /**
     * @return list<Response>
     */
    private function authResponses(): array
    {
        return [
            new Response(200, [], $this->fixture('keycloak-login-form.html')),
            new Response(200, [], $this->fixture('oidc-callback.html')),
            new Response(200, [], $this->fixture('login-response.html')),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $history
     */
    private function createClient(MockHandler $mock, ?ArrayCache $cache = null, array &$history = []): Ista
    {
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($history));
        $httpClient = new Client(['handler' => $handlerStack]);

        return new Ista(
            username: 'test@example.com',
            password: 'password123',
            httpClient: $httpClient,
            cache: $cache,
        );
    }

    public function test_get_user_values_returns_correct_dtos(): void
    {
        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('user-values.json')),
        ]);

        $ista = $this->createClient($mock);
        $result = $ista->getUserValues();

        $this->assertInstanceOf(UserValuesResult::class, $result);
        $this->assertCount(1, $result->customers);
        $this->assertSame('CUST-12345', $result->customers[0]->cuid);
        $this->assertCount(1, $result->customers[0]->consumption->services);
        $this->assertSame(250, $result->customers[0]->consumption->services[0]->totalNow);
        $this->assertSame(230, $result->customers[0]->consumption->services[0]->totalPrevious);
        $this->assertCount(2, $result->customers[0]->consumption->services[0]->currentMeters);
    }

    public function test_get_consumption_averages_sends_correct_payload(): void
    {
        $history = [];

        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('consumption-averages.json')),
        ]);

        $ista = $this->createClient($mock, history: $history);
        $result = $ista->getConsumptionAverages(
            cuid: 'CUST-12345',
            start: new DateTimeImmutable('2024-01-01'),
            end: new DateTimeImmutable('2024-12-31'),
        );

        $this->assertInstanceOf(ConsumptionAverageResult::class, $result);
        $this->assertSame(915, $result->getNormalizedValue());

        // Verify the API request payload (3 auth + 1 API = 4 total)
        $this->assertCount(4, $history);
        $apiRequest = $history[3]['request'];
        $body = json_decode((string) $apiRequest->getBody(), true);

        $this->assertArrayHasKey('JWT', $body);
        $this->assertSame('CUST-12345', $body['Cuid']);
        $this->assertSame('2024-01-01', $body['PAR']['start']);
        $this->assertSame('2024-12-31', $body['PAR']['end']);
    }

    public function test_http_error_throws_ista_exception(): void
    {
        $mock = new MockHandler([
            ...$this->authResponses(),
            new ConnectException(
                'Connection refused',
                new Request('POST', 'https://mijn.ista.nl/api/Values/UserValues'),
            ),
        ]);

        $ista = $this->createClient($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('API request failed');

        $ista->getUserValues();
    }

    public function test_invalid_json_response_throws_ista_exception(): void
    {
        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], 'not-json'),
        ]);

        $ista = $this->createClient($mock);

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('Failed to decode API response');

        $ista->getUserValues();
    }

    public function test_no_content_response_returns_empty_result(): void
    {
        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(204),
        ]);

        $ista = $this->createClient($mock);
        $result = $ista->getConsumptionAverages(
            cuid: 'CUST-12345',
            start: new DateTimeImmutable('2024-01-01'),
            end: new DateTimeImmutable('2024-12-31'),
        );

        $this->assertInstanceOf(ConsumptionAverageResult::class, $result);
        $this->assertSame([], $result->averages);
        $this->assertNull($result->getNormalizedValue());
    }

    public function test_responses_are_cached(): void
    {
        $cache = new ArrayCache;

        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('user-values.json')),
        ]);

        $ista = $this->createClient($mock, $cache);

        // First call
        $result1 = $ista->getUserValues();

        // Second call should use cache (no more HTTP responses queued)
        $result2 = $ista->getUserValues();

        $this->assertSame($result1->customers[0]->cuid, $result2->customers[0]->cuid);
    }

    public function test_token_is_reused_across_calls(): void
    {
        $history = [];

        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('user-values.json')),
            new Response(200, [], $this->fixture('consumption-averages.json')),
        ]);

        $ista = $this->createClient($mock, history: $history);

        $ista->getUserValues();
        $ista->getConsumptionAverages(
            cuid: 'CUST-12345',
            start: new DateTimeImmutable('2024-01-01'),
            end: new DateTimeImmutable('2024-12-31'),
        );

        // 3 auth calls + 2 API calls = 5 total
        $this->assertCount(5, $history);
    }

    public function test_get_monthly_consumption_returns_correct_dtos(): void
    {
        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('month-values.json')),
        ]);

        $ista = $this->createClient($mock);
        $result = $ista->getMonthlyConsumption('CUST-12345');

        $this->assertInstanceOf(MonthlyConsumptionResult::class, $result);
        $this->assertSame('CUST-12345', $result->cuid);
        $this->assertSame(36, $result->showMonths);
        $this->assertCount(2, $result->months);
        $this->assertSame(2026, $result->months[0]->year);
        $this->assertSame(3, $result->months[0]->month);
        $this->assertCount(1, $result->months[0]->serviceConsumptions);
    }

    public function test_get_monthly_consumption_sends_correct_payload(): void
    {
        $history = [];

        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('month-values.json')),
        ]);

        $ista = $this->createClient($mock, history: $history);
        $ista->getMonthlyConsumption('CUST-12345');

        $this->assertCount(4, $history);
        $apiRequest = $history[3]['request'];
        $body = json_decode((string) $apiRequest->getBody(), true);

        $this->assertArrayHasKey('JWT', $body);
        $this->assertSame('CUST-12345', $body['Cuid']);
    }

    public function test_monthly_consumption_is_cached(): void
    {
        $cache = new ArrayCache;

        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('month-values.json')),
        ]);

        $ista = $this->createClient($mock, $cache);

        $result1 = $ista->getMonthlyConsumption('CUST-12345');
        $result2 = $ista->getMonthlyConsumption('CUST-12345');

        $this->assertSame($result1->cuid, $result2->cuid);
        $this->assertSame($result1->toArray(), $result2->toArray());
    }

    public function test_get_consumption_values_returns_correct_dtos(): void
    {
        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('consumption-values.json')),
        ]);

        $ista = $this->createClient($mock);
        $result = $ista->getConsumptionValues('CUST-12345', 2025, '2025-01-01T00:00:00', '2025-12-31T00:00:00');

        $this->assertInstanceOf(ConsumptionValuesResult::class, $result);
        $this->assertSame('01-01-2025', $result->curStart);
        $this->assertSame('31-12-2025', $result->curEnd);
        $this->assertCount(1, $result->services);
        $this->assertSame(1, $result->services[0]->id);
    }

    public function test_get_consumption_values_sends_correct_payload(): void
    {
        $history = [];

        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('consumption-values.json')),
        ]);

        $ista = $this->createClient($mock, history: $history);
        $ista->getConsumptionValues('CUST-12345', 2025, '2025-01-01T00:00:00', '2025-12-31T00:00:00');

        $this->assertCount(4, $history);
        $apiRequest = $history[3]['request'];
        $body = json_decode((string) $apiRequest->getBody(), true);

        $this->assertArrayHasKey('JWT', $body);
        $this->assertSame('CUST-12345', $body['Cuid']);
        $this->assertSame(2025, $body['Billingperiod']['y']);
        $this->assertSame('2025-01-01T00:00:00', $body['Billingperiod']['s']);
        $this->assertSame('2025-12-31T00:00:00', $body['Billingperiod']['e']);
    }

    public function test_consumption_values_is_cached(): void
    {
        $cache = new ArrayCache;

        $mock = new MockHandler([
            ...$this->authResponses(),
            new Response(200, [], $this->fixture('consumption-values.json')),
        ]);

        $ista = $this->createClient($mock, $cache);

        $result1 = $ista->getConsumptionValues('CUST-12345', 2025, '2025-01-01T00:00:00', '2025-12-31T00:00:00');
        $result2 = $ista->getConsumptionValues('CUST-12345', 2025, '2025-01-01T00:00:00', '2025-12-31T00:00:00');

        $this->assertSame($result1->toArray(), $result2->toArray());
    }
}
