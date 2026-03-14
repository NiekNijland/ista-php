<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\MonthlyConsumption;
use NiekNijland\Ista\Data\MonthlyConsumptionResult;
use PHPUnit\Framework\TestCase;

class MonthlyConsumptionResultTest extends TestCase
{
    public function test_from_array_creates_result(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/month-values.json'),
            true,
        );

        $result = MonthlyConsumptionResult::fromArray($data);

        $this->assertSame('CUST-12345', $result->cuid);
        $this->assertSame('2023-04-26T00:00:00', $result->startDate);
        $this->assertSame('2026-03-07T00:00:00', $result->endDate);
        $this->assertSame(36, $result->showMonths);
        $this->assertSame(36, $result->hasMonths);
        $this->assertSame([2026, 2025, 2024], $result->years);
        $this->assertCount(2, $result->months);
        $this->assertInstanceOf(MonthlyConsumption::class, $result->months[0]);
        $this->assertSame(2026, $result->months[0]->year);
        $this->assertSame(3, $result->months[0]->month);
    }

    public function test_to_array_round_trip(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/month-values.json'),
            true,
        );

        $result = MonthlyConsumptionResult::fromArray($data);

        $this->assertSame($data, $result->toArray());
    }

    public function test_empty_months(): void
    {
        $result = MonthlyConsumptionResult::fromArray([
            'cuid' => 'CUST-12345',
            'sd' => '2023-01-01T00:00:00',
            'ed' => '2026-01-01T00:00:00',
            'sh' => 36,
            'hs' => 0,
            'ys' => [],
            'mc' => [],
        ]);

        $this->assertSame([], $result->months);
        $this->assertSame([], $result->years);
        $this->assertSame(0, $result->hasMonths);
    }

    public function test_missing_fields_use_defaults(): void
    {
        $result = MonthlyConsumptionResult::fromArray([]);

        $this->assertSame('', $result->cuid);
        $this->assertSame('', $result->startDate);
        $this->assertSame('', $result->endDate);
        $this->assertSame(0, $result->showMonths);
        $this->assertSame(0, $result->hasMonths);
        $this->assertSame([], $result->years);
        $this->assertSame([], $result->months);
    }

    public function test_months_contain_service_and_device_data(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/month-values.json'),
            true,
        );

        $result = MonthlyConsumptionResult::fromArray($data);

        // Second month has 2 services, first with 2 devices
        $secondMonth = $result->months[1];
        $this->assertSame(2026, $secondMonth->year);
        $this->assertSame(2, $secondMonth->month);
        $this->assertCount(2, $secondMonth->serviceConsumptions);

        $firstService = $secondMonth->serviceConsumptions[0];
        $this->assertSame(1, $firstService->serviceId);
        $this->assertSame(45, $firstService->totalConsumption);
        $this->assertCount(2, $firstService->deviceConsumptions);
        $this->assertSame('Hal', $firstService->deviceConsumptions[0]->roomDescription);
        $this->assertSame('Woonkamer', $firstService->deviceConsumptions[1]->roomDescription);

        $secondService = $secondMonth->serviceConsumptions[1];
        $this->assertSame(2, $secondService->serviceId);
        $this->assertTrue($secondService->hasApproximation);
        $this->assertSame('Badkamer', $secondService->deviceConsumptions[0]->roomDescription);
    }
}
