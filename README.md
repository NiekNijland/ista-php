# Ista PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nieknijland/ista-php.svg?style=flat-square)](https://packagist.org/packages/nieknijland/ista-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/nieknijland/ista-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nieknijland/ista-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/nieknijland/ista-php.svg?style=flat-square)](https://packagist.org/packages/nieknijland/ista-php)

PHP client for the Ista energy consumption API at `mijn.ista.nl`. Framework-agnostic.

Requires PHP 8.4+.

## Installation

```bash
composer require nieknijland/ista-php
```

## Quick start

```php
use NiekNijland\Ista\Ista;

$ista = new Ista(
    username: 'your@email.com',
    password: 'your-password',
);

// Fetch all consumption data
$userValues = $ista->getUserValues();

$customer = $userValues->customers[0];

foreach ($customer->consumption->services as $service) {
    echo "Current usage: {$service->totalNow}\n";
    echo "Previous year: {$service->totalPrevious}\n";

    foreach ($service->currentMeters as $meter) {
        echo "  {$meter->position}: {$meter->value}\n";
    }
}
```

## Constructor

```php
$ista = new Ista(
    username: 'your@email.com',
    password: 'your-password',
    httpClient: $customGuzzleClient,  // ?ClientInterface, default: new Client()
    cache: $psr16Cache,               // ?CacheInterface (PSR-16), default: null (no caching)
    cacheTtl: 3600,                   // int, cache TTL in seconds, default: 3600
);
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$username` | `string` | *(required)* | Your mijn.ista.nl username |
| `$password` | `string` | *(required)* | Your mijn.ista.nl password (marked `#[SensitiveParameter]`) |
| `$httpClient` | `?ClientInterface` | `null` | Custom Guzzle HTTP client; `null` creates a default one |
| `$cache` | `?CacheInterface` | `null` | Any PSR-16 cache implementation; `null` disables caching |
| `$cacheTtl` | `int` | `3600` | Cache time-to-live in seconds |

## API methods

The client provides four methods. All throw `IstaException` on failure.

| Method | Description |
|--------|-------------|
| [`getUserValues()`](docs/api-methods.md#getuservalues) | Fetch all customers, meters, and billing data |
| [`getConsumptionAverages()`](docs/api-methods.md#getconsumptionaverages) | Fetch building average consumption |
| [`getMonthlyConsumption()`](docs/api-methods.md#getmonthlyconsumption) | Fetch month-by-month consumption history |
| [`getConsumptionValues()`](docs/api-methods.md#getconsumptionvalues) | Fetch consumption for a specific billing period |

See [API Methods](docs/api-methods.md) for full parameter documentation and examples.

## Data objects

All API responses are returned as readonly DTOs with typed properties. Every DTO provides `fromArray()` and `toArray()` for serialization.

| Class | Description |
|-------|-------------|
| [`UserValuesResult`](docs/data-objects.md#uservaluesresult) | Top-level response with customers |
| [`Customer`](docs/data-objects.md#customer) | A customer with address and consumption data |
| [`ConsumptionPeriod`](docs/data-objects.md#consumptionperiod) | A billing period with services, meters, and temperatures |
| [`ServiceComparison`](docs/data-objects.md#servicecomparison) | Current vs. previous year totals for a service type |
| [`Meter`](docs/data-objects.md#meter) | A single meter reading with all technical fields |
| [`BillingService`](docs/data-objects.md#billingservice) | Service type definition (e.g. Heating, Hot Water) |
| [`BillingPeriod`](docs/data-objects.md#billingperiod) | A billing year with start/end dates |
| [`ConsumptionAverageResult`](docs/data-objects.md#consumptionaverageresult) | Building average consumption |
| [`ConsumptionValuesResult`](docs/data-objects.md#consumptionvaluesresult) | Consumption data for a specific billing period |
| [`MonthlyConsumptionResult`](docs/data-objects.md#monthlyconsumptionresult) | Monthly consumption history |
| [`MonthlyConsumption`](docs/data-objects.md#monthlyconsumption) | A single month's consumption |
| [`MonthlyServiceConsumption`](docs/data-objects.md#monthlyserviceconsumption) | Per-service totals for a month |
| [`MonthlyDeviceConsumption`](docs/data-objects.md#monthlydeviceconsumption) | Per-device readings for a month |

See [Data Objects](docs/data-objects.md) for all properties and types.

## Caching

Pass any PSR-16 `CacheInterface` to cache API responses and the JWT token:

```php
$ista = new Ista(
    username: 'your@email.com',
    password: 'your-password',
    cache: new YourPsr16Cache(),
    cacheTtl: 86400, // 24 hours
);
```

Cache keys used:

| Key pattern | Data |
|-------------|------|
| `ista:jwt` | Authentication token |
| `ista:user-values` | `getUserValues()` response |
| `ista:consumption-averages:{cuid}:{start}:{end}` | `getConsumptionAverages()` response |
| `ista:monthly-consumption:{cuid}` | `getMonthlyConsumption()` response |
| `ista:consumption-values:{cuid}:{year}:{start}:{end}` | `getConsumptionValues()` response |

Cache failures are silently ignored -- the client will re-fetch from the API.

## Error handling

All errors are wrapped in a single exception class:

```php
use NiekNijland\Ista\Exception\IstaException;

try {
    $result = $ista->getUserValues();
} catch (IstaException $e) {
    // Authentication failures, HTTP errors, malformed responses
    // Original exception is available via $e->getPrevious()
}
```

## Testing

The package ships testing utilities so you can mock the Ista client in your application tests without making real API calls.

```php
use NiekNijland\Ista\Testing\FakeIsta;
use NiekNijland\Ista\Testing\UserValuesResultFactory;

$fake = new FakeIsta();
$fake->seedUserValuesResult(UserValuesResultFactory::make());

$result = $fake->getUserValues();

$fake->assertCalled('getUserValues');
$fake->assertCalledTimes('getUserValues', 1);
```

See [Testing](docs/testing.md) for full documentation of `FakeIsta`, all factories, and recorded call inspection.

## Development

```bash
composer test              # Unit tests
composer test-integration  # Integration tests (requires ISTA_USERNAME and ISTA_PASSWORD)
composer test-all          # All test suites
composer analyse           # PHPStan level 8
composer format            # Laravel Pint
composer rector            # Rector automated refactoring
composer codestyle         # Full pipeline: Rector + Pint + PHPStan
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
