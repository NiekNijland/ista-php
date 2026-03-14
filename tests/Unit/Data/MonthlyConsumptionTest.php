<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\MonthlyConsumption;
use NiekNijland\Ista\Data\MonthlyServiceConsumption;
use PHPUnit\Framework\TestCase;

class MonthlyConsumptionTest extends TestCase
{
    public function test_from_array_creates_monthly_consumption(): void
    {
        $data = [
            'y' => 2026,
            'm' => 3,
            'at' => 8.9,
            'CuToken' => 'token-2026-03',
            'ServiceConsumptions' => [
                [
                    'ServiceId' => 1,
                    'TotalConsumption' => 8,
                    'BuldingAverage' => 36,
                    'HasApproximation' => false,
                    'DeviceConsumptions' => [],
                ],
            ],
        ];

        $month = MonthlyConsumption::fromArray($data);

        $this->assertSame(2026, $month->year);
        $this->assertSame(3, $month->month);
        $this->assertSame(8.9, $month->averageTemperature);
        $this->assertSame('token-2026-03', $month->cuToken);
        $this->assertCount(1, $month->serviceConsumptions);
        $this->assertInstanceOf(MonthlyServiceConsumption::class, $month->serviceConsumptions[0]);
    }

    public function test_round_trip(): void
    {
        $data = [
            'y' => 2026,
            'm' => 2,
            'at' => 5.2,
            'CuToken' => 'token-2026-02',
            'ServiceConsumptions' => [
                [
                    'ServiceId' => 1,
                    'TotalConsumption' => 45,
                    'BuldingAverage' => 120,
                    'HasApproximation' => false,
                    'DeviceConsumptions' => [],
                ],
            ],
        ];

        $month = MonthlyConsumption::fromArray($data);

        $this->assertSame($data, $month->toArray());
    }

    public function test_missing_fields_use_defaults(): void
    {
        $month = MonthlyConsumption::fromArray([]);

        $this->assertSame(0, $month->year);
        $this->assertSame(0, $month->month);
        $this->assertSame(0.0, $month->averageTemperature);
        $this->assertSame('', $month->cuToken);
        $this->assertSame([], $month->serviceConsumptions);
    }
}
