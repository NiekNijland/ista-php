<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Integration;

use DateTimeImmutable;
use NiekNijland\Ista\Data\ConsumptionAverageResult;
use NiekNijland\Ista\Data\ConsumptionPeriod;
use NiekNijland\Ista\Data\ConsumptionValuesResult;
use NiekNijland\Ista\Data\Customer;
use NiekNijland\Ista\Data\Meter;
use NiekNijland\Ista\Data\MonthlyConsumption;
use NiekNijland\Ista\Data\MonthlyConsumptionResult;
use NiekNijland\Ista\Data\ServiceComparison;
use NiekNijland\Ista\Data\UserValuesResult;
use NiekNijland\Ista\Exception\IstaException;
use NiekNijland\Ista\Ista;
use NiekNijland\Ista\Tests\ArrayCache;
use PHPUnit\Framework\TestCase;

/**
 * Live API tests - excluded from the default test suite.
 * Run with: composer test-integration
 *
 * Requires environment variables:
 * - ISTA_USERNAME
 * - ISTA_PASSWORD
 */
class IntegrationTest extends TestCase
{
    private function createClient(?ArrayCache $cache = null): Ista
    {
        $username = getenv('ISTA_USERNAME');
        $password = getenv('ISTA_PASSWORD');

        if (! is_string($username) || $username === '' || ! is_string($password) || $password === '') {
            $this->markTestSkipped('ISTA_USERNAME and ISTA_PASSWORD environment variables are required.');
        }

        return new Ista($username, $password, cache: $cache);
    }

    private function fetchFirstCustomer(Ista $ista): Customer
    {
        $result = $ista->getUserValues();

        $this->assertNotEmpty($result->customers, 'Account should have at least one customer.');

        return $result->customers[0];
    }

    // ---------------------------------------------------------------
    // getUserValues
    // ---------------------------------------------------------------

    public function test_get_user_values_returns_result(): void
    {
        $ista = $this->createClient();
        $result = $ista->getUserValues();

        $this->assertInstanceOf(UserValuesResult::class, $result);
        $this->assertNotEmpty($result->customers);
    }

    public function test_customers_have_required_properties(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertNotEmpty($customer->cuid, 'Customer CUID should not be empty.');
        $this->assertInstanceOf(ConsumptionPeriod::class, $customer->consumption);
    }

    public function test_consumption_period_has_valid_dates(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);
        $period = $customer->consumption;

        $this->assertInstanceOf(DateTimeImmutable::class, $period->start);
        $this->assertInstanceOf(DateTimeImmutable::class, $period->end);
        $this->assertGreaterThan($period->start, $period->end, 'End date should be after start date.');
    }

    public function test_consumption_period_has_services(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $this->assertNotEmpty($customer->consumption->services, 'Customer should have at least one service.');

        $service = $customer->consumption->services[0];
        $this->assertInstanceOf(ServiceComparison::class, $service);
    }

    public function test_service_comparison_has_meters(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);
        $service = $customer->consumption->services[0];

        $this->assertNotEmpty($service->currentMeters, 'Service should have at least one current meter.');

        $meter = $service->currentMeters[0];
        $this->assertInstanceOf(Meter::class, $meter);
        $this->assertNotEmpty($meter->meterId, 'Meter ID should not be empty.');
        $this->assertNotEmpty($meter->position, 'Meter position should not be empty.');
    }

    public function test_service_comparison_totals_are_non_negative(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);
        $service = $customer->consumption->services[0];

        $this->assertGreaterThanOrEqual(0, $service->totalNow);
        $this->assertGreaterThanOrEqual(0, $service->totalPrevious);
    }

    // ---------------------------------------------------------------
    // getConsumptionAverages
    // ---------------------------------------------------------------

    public function test_get_consumption_averages_returns_result(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $result = $ista->getConsumptionAverages(
            cuid: $customer->cuid,
            start: $customer->consumption->start,
            end: $customer->consumption->end,
        );

        $this->assertInstanceOf(ConsumptionAverageResult::class, $result);
        $this->assertIsArray($result->averages);
    }

    public function test_consumption_averages_contain_normalized_values_when_available(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $result = $ista->getConsumptionAverages(
            cuid: $customer->cuid,
            start: $customer->consumption->start,
            end: $customer->consumption->end,
        );

        if ($result->averages === []) {
            $this->assertNull($result->getNormalizedValue(0));

            return;
        }

        $this->assertIsArray($result->averages[0]);
        $this->assertArrayHasKey('NormalizedValue', $result->averages[0]);
    }

    public function test_get_normalized_value_returns_null_for_missing_index(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $result = $ista->getConsumptionAverages(
            cuid: $customer->cuid,
            start: $customer->consumption->start,
            end: $customer->consumption->end,
        );

        $this->assertNull($result->getNormalizedValue(999));
    }

    // ---------------------------------------------------------------
    // Round-trip serialization
    // ---------------------------------------------------------------

    public function test_user_values_round_trip_serialization(): void
    {
        $ista = $this->createClient();
        $result = $ista->getUserValues();

        $serialized = $result->toArray();
        $deserialized = UserValuesResult::fromArray($serialized);

        $this->assertSame($serialized, $deserialized->toArray());
    }

    public function test_consumption_averages_round_trip_serialization(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $result = $ista->getConsumptionAverages(
            cuid: $customer->cuid,
            start: $customer->consumption->start,
            end: $customer->consumption->end,
        );

        $serialized = $result->toArray();
        $deserialized = ConsumptionAverageResult::fromArray($serialized);

        $this->assertSame($serialized, $deserialized->toArray());

        // Empty result should also round-trip cleanly
        if ($result->averages === []) {
            $this->assertSame(['Averages' => []], $serialized);
        }
    }

    // ---------------------------------------------------------------
    // Caching
    // ---------------------------------------------------------------

    public function test_second_call_returns_cached_user_values(): void
    {
        $cache = new ArrayCache;
        $ista = $this->createClient($cache);

        $first = $ista->getUserValues();
        $this->assertTrue($cache->has('ista:user-values'), 'User values should be cached after first call.');

        $second = $ista->getUserValues();
        $this->assertSame($first->toArray(), $second->toArray());
    }

    public function test_second_call_returns_cached_consumption_averages(): void
    {
        $cache = new ArrayCache;
        $ista = $this->createClient($cache);
        $customer = $this->fetchFirstCustomer($ista);

        $first = $ista->getConsumptionAverages(
            cuid: $customer->cuid,
            start: $customer->consumption->start,
            end: $customer->consumption->end,
        );

        $second = $ista->getConsumptionAverages(
            cuid: $customer->cuid,
            start: $customer->consumption->start,
            end: $customer->consumption->end,
        );

        $this->assertSame($first->toArray(), $second->toArray());
    }

    // ---------------------------------------------------------------
    // Error handling
    // ---------------------------------------------------------------

    public function test_invalid_credentials_throw_ista_exception(): void
    {
        $this->expectException(IstaException::class);

        $ista = new Ista('invalid@example.com', 'wrong-password');
        $ista->getUserValues();
    }

    // ---------------------------------------------------------------
    // getMonthlyConsumption
    // ---------------------------------------------------------------

    public function test_get_monthly_consumption_returns_result(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $result = $ista->getMonthlyConsumption($customer->cuid);

        $this->assertInstanceOf(MonthlyConsumptionResult::class, $result);
        $this->assertSame($customer->cuid, $result->cuid);
        $this->assertNotEmpty($result->months);
        $this->assertNotEmpty($result->years);
        $this->assertGreaterThan(0, $result->showMonths);
        $this->assertGreaterThan(0, $result->hasMonths);
    }

    public function test_monthly_consumption_months_have_expected_structure(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $result = $ista->getMonthlyConsumption($customer->cuid);

        $month = $result->months[0];
        $this->assertInstanceOf(MonthlyConsumption::class, $month);
        $this->assertGreaterThan(0, $month->year);
        $this->assertGreaterThanOrEqual(1, $month->month);
        $this->assertLessThanOrEqual(12, $month->month);
        $this->assertNotEmpty($month->serviceConsumptions);

        $service = $month->serviceConsumptions[0];
        $this->assertGreaterThan(0, $service->serviceId);
        $this->assertNotEmpty($service->deviceConsumptions);

        $device = $service->deviceConsumptions[0];
        $this->assertGreaterThan(0, $device->id);
        $this->assertNotEmpty($device->roomDescription);
    }

    public function test_monthly_consumption_round_trip_serialization(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $result = $ista->getMonthlyConsumption($customer->cuid);

        $serialized = $result->toArray();
        $deserialized = MonthlyConsumptionResult::fromArray($serialized);

        $this->assertSame($serialized, $deserialized->toArray());
    }

    public function test_second_call_returns_cached_monthly_consumption(): void
    {
        $cache = new ArrayCache;
        $ista = $this->createClient($cache);
        $customer = $this->fetchFirstCustomer($ista);

        $first = $ista->getMonthlyConsumption($customer->cuid);

        $cacheKey = 'ista:monthly-consumption:'.$customer->cuid;
        $this->assertTrue($cache->has($cacheKey), 'Monthly consumption should be cached after first call.');

        $second = $ista->getMonthlyConsumption($customer->cuid);
        $this->assertSame($first->toArray(), $second->toArray());
    }

    // ---------------------------------------------------------------
    // getConsumptionValues
    // ---------------------------------------------------------------

    public function test_get_consumption_values_returns_result(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);
        $billingPeriod = $customer->consumption->billingPeriods[0];

        $result = $ista->getConsumptionValues(
            cuid: $customer->cuid,
            year: $billingPeriod->year,
            start: $billingPeriod->start,
            end: $billingPeriod->end,
        );

        $this->assertInstanceOf(ConsumptionValuesResult::class, $result);
        $this->assertNotEmpty($result->curStart);
        $this->assertNotEmpty($result->curEnd);
        $this->assertNotEmpty($result->services);
    }

    public function test_consumption_values_round_trip_serialization(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);
        $billingPeriod = $customer->consumption->billingPeriods[0];

        $result = $ista->getConsumptionValues(
            cuid: $customer->cuid,
            year: $billingPeriod->year,
            start: $billingPeriod->start,
            end: $billingPeriod->end,
        );

        $serialized = $result->toArray();
        $deserialized = ConsumptionValuesResult::fromArray($serialized);

        $this->assertSame($serialized, $deserialized->toArray());
    }

    public function test_second_call_returns_cached_consumption_values(): void
    {
        $cache = new ArrayCache;
        $ista = $this->createClient($cache);
        $customer = $this->fetchFirstCustomer($ista);
        $billingPeriod = $customer->consumption->billingPeriods[0];

        $first = $ista->getConsumptionValues(
            cuid: $customer->cuid,
            year: $billingPeriod->year,
            start: $billingPeriod->start,
            end: $billingPeriod->end,
        );

        $second = $ista->getConsumptionValues(
            cuid: $customer->cuid,
            year: $billingPeriod->year,
            start: $billingPeriod->start,
            end: $billingPeriod->end,
        );

        $this->assertSame($first->toArray(), $second->toArray());
    }

    // ---------------------------------------------------------------
    // Expanded fields
    // ---------------------------------------------------------------

    public function test_customer_has_address_fields(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        // These may be empty for some accounts, but the fields should exist
        $this->assertIsString($customer->address);
        $this->assertIsString($customer->zip);
        $this->assertIsString($customer->city);
        $this->assertIsString($customer->dateStart);
    }

    public function test_user_values_has_display_name(): void
    {
        $ista = $this->createClient();
        $result = $ista->getUserValues();

        $this->assertIsString($result->displayName);
    }

    public function test_consumption_period_has_comparison_fields(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);
        $period = $customer->consumption;

        $this->assertIsString($period->compStart);
        $this->assertIsString($period->compEnd);
        $this->assertIsFloat($period->curAverageTemp);
        $this->assertIsFloat($period->compAverageTemp);
        $this->assertIsArray($period->billingServices);
        $this->assertIsArray($period->billingPeriods);
    }

    public function test_meters_have_expanded_fields(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);
        $service = $customer->consumption->services[0];
        $meter = $service->currentMeters[0];

        $this->assertIsInt($meter->meterNr);
        $this->assertIsInt($meter->artNr);
        $this->assertIsFloat($meter->calcFactor);
        $this->assertIsInt($meter->beginValue);
        $this->assertIsInt($meter->endValue);
        $this->assertIsString($meter->beginDate);
        $this->assertIsString($meter->endDate);
        $this->assertIsInt($meter->serviceId);
        $this->assertIsInt($meter->billingPeriodId);
        $this->assertIsInt($meter->radiatorNumber);
        $this->assertIsInt($meter->order);
        $this->assertIsInt($meter->transferLoss);
        $this->assertIsInt($meter->multiply);
        $this->assertIsInt($meter->decimalPositions);
        $this->assertIsInt($meter->estimatedStartValue);
        $this->assertIsInt($meter->estimatedEndValue);
    }

    public function test_service_comparison_has_id_and_decimal_positions(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);
        $service = $customer->consumption->services[0];

        $this->assertIsInt($service->id);
        $this->assertIsInt($service->decimalPositions);
    }

    public function test_consumption_averages_have_cur_start_and_end(): void
    {
        $ista = $this->createClient();
        $customer = $this->fetchFirstCustomer($ista);

        $result = $ista->getConsumptionAverages(
            cuid: $customer->cuid,
            start: $customer->consumption->start,
            end: $customer->consumption->end,
        );

        $this->assertIsString($result->curStart);
        $this->assertIsString($result->curEnd);
    }
}
