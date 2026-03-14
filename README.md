# ISTA PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nieknijland/ista-php.svg?style=flat-square)](https://packagist.org/packages/nieknijland/ista-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/nieknijland/ista-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nieknijland/ista-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/nieknijland/ista-php.svg?style=flat-square)](https://packagist.org/packages/nieknijland/ista-php)

PHP client for the ISTA energy consumption API at `mijn.ista.nl`. Framework-agnostic, zero Laravel dependencies.

## Installation

```bash
composer require nieknijland/ista-php
```

## Usage

```php
use NiekNijland\ISTA\Ista;

$ista = new Ista(
    username: 'your@email.com',
    password: 'your-password',
);

// Get consumption data
$userValues = $ista->getUserValues();

$customer = $userValues->customers[0];
echo $customer->cuid;
echo $customer->consumption->start->format('Y-m-d');

foreach ($customer->consumption->services as $service) {
    echo "Current usage: {$service->totalNow}";
    echo "Previous year: {$service->totalPrevious}";

    foreach ($service->currentMeters as $meter) {
        echo "{$meter->position}: {$meter->value}";
    }
}

// Get building average
$averages = $ista->getConsumptionAverages(
    cuid: $customer->cuid,
    start: $customer->consumption->start,
    end: $customer->consumption->end,
);

echo "Building average: {$averages->getNormalizedValue()}";
```

### Constructor Options

```php
$ista = new Ista(
    username: 'your@email.com',
    password: 'your-password',
    httpClient: $customGuzzleClient,  // ?ClientInterface, defaults to new Client()
    cache: $psr16Cache,               // ?CacheInterface (PSR-16), defaults to null
    cacheTtl: 3600,                   // Cache TTL in seconds, defaults to 3600
);
```

### Caching

Pass any PSR-16 `CacheInterface` implementation to cache API responses and the JWT token:

```php
use NiekNijland\ISTA\Ista;

$ista = new Ista(
    username: 'your@email.com',
    password: 'your-password',
    cache: new YourPsr16Cache(),
    cacheTtl: 86400, // 24 hours
);
```

The JWT token is cached separately to avoid re-authenticating on every call.

### Error Handling

All errors are wrapped in `IstaException`:

```php
use NiekNijland\ISTA\Exception\IstaException;

try {
    $result = $ista->getUserValues();
} catch (IstaException $e) {
    // Authentication failures, HTTP errors, malformed responses
}
```

## Testing

The package ships with testing utilities for use in your application tests.

### FakeIsta

```php
use NiekNijland\ISTA\Testing\FakeIsta;
use NiekNijland\ISTA\Testing\UserValuesResultFactory;
use NiekNijland\ISTA\Testing\ConsumptionAverageResultFactory;

$fake = new FakeIsta();

// Seed responses
$fake->seedUserValuesResult(UserValuesResultFactory::make());
$fake->seedConsumptionAverageResult(ConsumptionAverageResultFactory::make());

// Use in place of the real client (implements IstaInterface)
$result = $fake->getUserValues();

// Assertions
$fake->assertCalled('getUserValues');
$fake->assertCalledTimes('getUserValues', 1);
$fake->assertNotCalled('getConsumptionAverages');

// Simulate errors
$fake->shouldThrow(new IstaException('API down'));
```

### Factories

All DTOs have corresponding factories:

```php
use NiekNijland\ISTA\Testing\MeterFactory;
use NiekNijland\ISTA\Testing\ServiceComparisonFactory;
use NiekNijland\ISTA\Testing\ConsumptionPeriodFactory;
use NiekNijland\ISTA\Testing\CustomerFactory;
use NiekNijland\ISTA\Testing\UserValuesResultFactory;
use NiekNijland\ISTA\Testing\ConsumptionAverageResultFactory;

$meter = MeterFactory::make(meterId: 'CUSTOM-001', value: 999);
$customer = CustomerFactory::make(cuid: 'MY-CUID');
$result = UserValuesResultFactory::make(customers: [$customer]);
```

### Running Package Tests

```bash
composer test              # Unit tests
composer test-integration  # Integration tests (requires credentials)
composer test-all          # All tests
composer analyse           # PHPStan
composer codestyle         # Rector + Pint + PHPStan
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
