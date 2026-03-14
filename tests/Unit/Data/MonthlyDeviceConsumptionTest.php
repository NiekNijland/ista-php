<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\MonthlyDeviceConsumption;
use PHPUnit\Framework\TestCase;

class MonthlyDeviceConsumptionTest extends TestCase
{
    public function test_from_array_creates_device_consumption(): void
    {
        $device = MonthlyDeviceConsumption::fromArray([
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
        ]);

        $this->assertSame(1789316, $device->id);
        $this->assertSame(1, $device->output);
        $this->assertSame(11490, $device->artNr);
        $this->assertSame(918564311, $device->serialNr);
        $this->assertSame('2026-03-01T00:00:00', $device->startDate);
        $this->assertSame(0.0, $device->startValue);
        $this->assertSame('2026-03-07T00:00:00', $device->endDate);
        $this->assertSame(8.0, $device->endValue);
        $this->assertSame(8.0, $device->rawValue);
        $this->assertSame(8.0, $device->calculatedValue);
        $this->assertSame(8, $device->value);
        $this->assertSame('9998-12-31T00:00:00', $device->progJump);
        $this->assertSame('2020-03-02T00:00:00', $device->active);
        $this->assertSame('Hal', $device->roomDescription);
    }

    public function test_round_trip(): void
    {
        $data = [
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
        ];

        $device = MonthlyDeviceConsumption::fromArray($data);

        $this->assertSame($data, $device->toArray());
    }

    public function test_missing_fields_use_defaults(): void
    {
        $device = MonthlyDeviceConsumption::fromArray([]);

        $this->assertSame(0, $device->id);
        $this->assertSame(0, $device->output);
        $this->assertSame(0, $device->artNr);
        $this->assertSame(0, $device->serialNr);
        $this->assertSame('', $device->startDate);
        $this->assertSame(0.0, $device->startValue);
        $this->assertSame('', $device->endDate);
        $this->assertSame(0.0, $device->endValue);
        $this->assertSame(0.0, $device->rawValue);
        $this->assertSame(0.0, $device->calculatedValue);
        $this->assertSame(0, $device->value);
        $this->assertSame('', $device->progJump);
        $this->assertSame('', $device->active);
        $this->assertSame('', $device->roomDescription);
    }
}
