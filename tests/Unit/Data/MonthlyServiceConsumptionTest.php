<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\MonthlyDeviceConsumption;
use NiekNijland\Ista\Data\MonthlyServiceConsumption;
use PHPUnit\Framework\TestCase;

class MonthlyServiceConsumptionTest extends TestCase
{
    public function test_from_array_creates_service_consumption(): void
    {
        $data = [
            'ServiceId' => 1,
            'TotalConsumption' => 8,
            'BuldingAverage' => 36,
            'HasApproximation' => false,
            'DeviceConsumptions' => [
                [
                    'Id' => 1789316,
                    'Output' => 1,
                    'ArtNr' => 11490,
                    'SerialNr' => 918564311,
                    'SDate' => '2026-03-01T00:00:00',
                    'SValue' => 0.0,
                    'EDate' => '2026-03-07T00:00:00',
                    'EValue' => 8.0,
                    'CValue' => 8.0,
                    'CCValue' => 8.0,
                    'CCDValue' => 8,
                    'ProgJump' => '9998-12-31T00:00:00',
                    'Active' => '2020-03-02T00:00:00',
                    'RoomDescr' => 'Hal',
                ],
            ],
        ];

        $service = MonthlyServiceConsumption::fromArray($data);

        $this->assertSame(1, $service->serviceId);
        $this->assertSame(8, $service->totalConsumption);
        $this->assertSame(36, $service->buildingAverage);
        $this->assertFalse($service->hasApproximation);
        $this->assertCount(1, $service->deviceConsumptions);
        $this->assertInstanceOf(MonthlyDeviceConsumption::class, $service->deviceConsumptions[0]);
        $this->assertSame('Hal', $service->deviceConsumptions[0]->roomDescription);
    }

    public function test_round_trip(): void
    {
        $data = [
            'ServiceId' => 2,
            'TotalConsumption' => 3,
            'BuldingAverage' => 8,
            'HasApproximation' => true,
            'DeviceConsumptions' => [
                [
                    'Id' => 1789318,
                    'Output' => 1,
                    'ArtNr' => 22000,
                    'SerialNr' => 918564313,
                    'SDate' => '2026-02-01T00:00:00',
                    'SValue' => 50.0,
                    'EDate' => '2026-02-28T00:00:00',
                    'EValue' => 53.0,
                    'CValue' => 3.0,
                    'CCValue' => 3.0,
                    'CCDValue' => 3,
                    'ProgJump' => '9998-12-31T00:00:00',
                    'Active' => '2020-03-02T00:00:00',
                    'RoomDescr' => 'Badkamer',
                ],
            ],
        ];

        $service = MonthlyServiceConsumption::fromArray($data);

        $this->assertSame($data, $service->toArray());
    }

    public function test_missing_fields_use_defaults(): void
    {
        $service = MonthlyServiceConsumption::fromArray([]);

        $this->assertSame(0, $service->serviceId);
        $this->assertSame(0, $service->totalConsumption);
        $this->assertSame(0, $service->buildingAverage);
        $this->assertFalse($service->hasApproximation);
        $this->assertSame([], $service->deviceConsumptions);
    }
}
