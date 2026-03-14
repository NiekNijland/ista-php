<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Testing;

use NiekNijland\Ista\Data\BillingPeriod;
use NiekNijland\Ista\Data\BillingService;
use NiekNijland\Ista\Data\ConsumptionAverageResult;
use NiekNijland\Ista\Data\ConsumptionPeriod;
use NiekNijland\Ista\Data\ConsumptionValuesResult;
use NiekNijland\Ista\Data\Customer;
use NiekNijland\Ista\Data\Meter;
use NiekNijland\Ista\Data\MonthlyConsumption;
use NiekNijland\Ista\Data\MonthlyConsumptionResult;
use NiekNijland\Ista\Data\MonthlyDeviceConsumption;
use NiekNijland\Ista\Data\MonthlyServiceConsumption;
use NiekNijland\Ista\Data\ServiceComparison;
use NiekNijland\Ista\Data\UserValuesResult;
use NiekNijland\Ista\Testing\BillingPeriodFactory;
use NiekNijland\Ista\Testing\BillingServiceFactory;
use NiekNijland\Ista\Testing\ConsumptionAverageResultFactory;
use NiekNijland\Ista\Testing\ConsumptionPeriodFactory;
use NiekNijland\Ista\Testing\ConsumptionValuesResultFactory;
use NiekNijland\Ista\Testing\CustomerFactory;
use NiekNijland\Ista\Testing\MeterFactory;
use NiekNijland\Ista\Testing\MonthlyConsumptionFactory;
use NiekNijland\Ista\Testing\MonthlyConsumptionResultFactory;
use NiekNijland\Ista\Testing\MonthlyDeviceConsumptionFactory;
use NiekNijland\Ista\Testing\MonthlyServiceConsumptionFactory;
use NiekNijland\Ista\Testing\ServiceComparisonFactory;
use NiekNijland\Ista\Testing\UserValuesResultFactory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function test_meter_factory_creates_meter(): void
    {
        $meter = MeterFactory::make();

        $this->assertInstanceOf(Meter::class, $meter);
        $this->assertSame('MTR-001', $meter->meterId);
        $this->assertSame('Living Room', $meter->position);
        $this->assertSame(150, $meter->value);
        $this->assertSame(12345, $meter->meterNr);
        $this->assertSame(11490, $meter->artNr);
        $this->assertSame(1.2, $meter->calcFactor);
        $this->assertSame(1, $meter->serviceId);
        $this->assertSame(2024, $meter->billingPeriodId);
        $this->assertSame(1, $meter->radiatorNumber);
        $this->assertSame(1, $meter->order);
        $this->assertSame(0, $meter->transferLoss);
        $this->assertSame(1, $meter->multiply);
        $this->assertSame(0, $meter->decimalPositions);
        $this->assertSame(0, $meter->estimatedStartValue);
        $this->assertSame(0, $meter->estimatedEndValue);
    }

    public function test_meter_factory_accepts_overrides(): void
    {
        $meter = MeterFactory::make(meterId: 'CUSTOM-001', value: 999, radiatorNumber: 5);

        $this->assertSame('CUSTOM-001', $meter->meterId);
        $this->assertSame(999, $meter->value);
        $this->assertSame(5, $meter->radiatorNumber);
    }

    public function test_service_comparison_factory_creates_service(): void
    {
        $service = ServiceComparisonFactory::make();

        $this->assertInstanceOf(ServiceComparison::class, $service);
        $this->assertSame(1, $service->id);
        $this->assertSame(0, $service->decimalPositions);
        $this->assertSame(250, $service->totalNow);
        $this->assertSame(230, $service->totalPrevious);
        $this->assertSame(8, $service->totalDiffPercent);
        $this->assertSame(280, $service->totalWholePrevious);
        $this->assertCount(2, $service->currentMeters);
        $this->assertCount(2, $service->comparisonMeters);
    }

    public function test_consumption_period_factory_creates_period(): void
    {
        $period = ConsumptionPeriodFactory::make();

        $this->assertInstanceOf(ConsumptionPeriod::class, $period);
        $this->assertSame('2024-01-01', $period->start->format('Y-m-d'));
        $this->assertSame('2024-12-31', $period->end->format('Y-m-d'));
        $this->assertSame('01-01-2023', $period->compStart);
        $this->assertSame('31-12-2023', $period->compEnd);
        $this->assertSame(10.5, $period->curAverageTemp);
        $this->assertSame(9.8, $period->compAverageTemp);
        $this->assertCount(1, $period->services);
        $this->assertCount(1, $period->billingServices);
        $this->assertCount(1, $period->billingPeriods);
    }

    public function test_customer_factory_creates_customer(): void
    {
        $customer = CustomerFactory::make();

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame('CUST-12345', $customer->cuid);
        $this->assertSame('Teststraat 42', $customer->address);
        $this->assertSame('1234 AB', $customer->zip);
        $this->assertSame('Amsterdam', $customer->city);
        $this->assertSame('01-01-2020', $customer->dateStart);
        $this->assertInstanceOf(ConsumptionPeriod::class, $customer->consumption);
    }

    public function test_customer_factory_accepts_overrides(): void
    {
        $customer = CustomerFactory::make(cuid: 'CUSTOM-CUID');

        $this->assertSame('CUSTOM-CUID', $customer->cuid);
    }

    public function test_user_values_result_factory_creates_result(): void
    {
        $result = UserValuesResultFactory::make();

        $this->assertInstanceOf(UserValuesResult::class, $result);
        $this->assertSame('John Doe', $result->displayName);
        $this->assertCount(1, $result->customers);
    }

    public function test_consumption_average_result_factory_creates_result(): void
    {
        $result = ConsumptionAverageResultFactory::make();

        $this->assertInstanceOf(ConsumptionAverageResult::class, $result);
        $this->assertCount(2, $result->averages);
        $this->assertSame(915, $result->getNormalizedValue());
        $this->assertSame('01-01-2024', $result->curStart);
        $this->assertSame('31-12-2024', $result->curEnd);
    }

    public function test_consumption_average_result_factory_accepts_overrides(): void
    {
        $result = ConsumptionAverageResultFactory::make(averages: [
            ['NormalizedValue' => 500],
        ]);

        $this->assertCount(1, $result->averages);
        $this->assertSame(500, $result->getNormalizedValue(0));
    }

    public function test_billing_service_factory_creates_service(): void
    {
        $service = BillingServiceFactory::make();

        $this->assertInstanceOf(BillingService::class, $service);
        $this->assertSame(1, $service->id);
        $this->assertSame('Heating', $service->description);
        $this->assertSame('HKV', $service->meterType);
        $this->assertSame('Units', $service->unit);
    }

    public function test_billing_period_factory_creates_period(): void
    {
        $period = BillingPeriodFactory::make();

        $this->assertInstanceOf(BillingPeriod::class, $period);
        $this->assertSame(2024, $period->year);
        $this->assertSame('01-01-2024', $period->start);
        $this->assertSame('31-12-2024', $period->end);
        $this->assertSame(1250.50, $period->totalAmount);
    }

    public function test_monthly_device_consumption_factory_creates_device(): void
    {
        $device = MonthlyDeviceConsumptionFactory::make();

        $this->assertInstanceOf(MonthlyDeviceConsumption::class, $device);
        $this->assertSame(1789316, $device->id);
        $this->assertSame(918564311, $device->serialNr);
        $this->assertSame('Hal', $device->roomDescription);
    }

    public function test_monthly_service_consumption_factory_creates_service(): void
    {
        $service = MonthlyServiceConsumptionFactory::make();

        $this->assertInstanceOf(MonthlyServiceConsumption::class, $service);
        $this->assertSame(1, $service->serviceId);
        $this->assertSame(8, $service->totalConsumption);
        $this->assertSame(36, $service->buildingAverage);
        $this->assertFalse($service->hasApproximation);
        $this->assertCount(1, $service->deviceConsumptions);
    }

    public function test_monthly_consumption_factory_creates_month(): void
    {
        $month = MonthlyConsumptionFactory::make();

        $this->assertInstanceOf(MonthlyConsumption::class, $month);
        $this->assertSame(2026, $month->year);
        $this->assertSame(3, $month->month);
        $this->assertSame(8.9, $month->averageTemperature);
        $this->assertCount(1, $month->serviceConsumptions);
    }

    public function test_monthly_consumption_result_factory_creates_result(): void
    {
        $result = MonthlyConsumptionResultFactory::make();

        $this->assertInstanceOf(MonthlyConsumptionResult::class, $result);
        $this->assertSame('CUST-12345', $result->cuid);
        $this->assertSame(36, $result->showMonths);
        $this->assertSame([2026, 2025, 2024], $result->years);
        $this->assertCount(1, $result->months);
    }

    public function test_monthly_consumption_result_factory_accepts_overrides(): void
    {
        $result = MonthlyConsumptionResultFactory::make(
            cuid: 'CUSTOM-CUID',
            showMonths: 12,
            years: [2026],
        );

        $this->assertSame('CUSTOM-CUID', $result->cuid);
        $this->assertSame(12, $result->showMonths);
        $this->assertSame([2026], $result->years);
    }

    public function test_consumption_values_result_factory_creates_result(): void
    {
        $result = ConsumptionValuesResultFactory::make();

        $this->assertInstanceOf(ConsumptionValuesResult::class, $result);
        $this->assertSame('01-01-2025', $result->curStart);
        $this->assertSame('31-12-2025', $result->curEnd);
        $this->assertCount(1, $result->services);
        $this->assertInstanceOf(ServiceComparison::class, $result->services[0]);
    }

    public function test_consumption_values_result_factory_accepts_overrides(): void
    {
        $result = ConsumptionValuesResultFactory::make(
            curStart: '01-06-2025',
            curEnd: '30-06-2025',
            services: [],
        );

        $this->assertSame('01-06-2025', $result->curStart);
        $this->assertSame('30-06-2025', $result->curEnd);
        $this->assertSame([], $result->services);
    }
}
