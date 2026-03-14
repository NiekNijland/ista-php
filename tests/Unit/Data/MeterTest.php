<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\Meter;
use PHPUnit\Framework\TestCase;

class MeterTest extends TestCase
{
    public function test_from_array_creates_meter(): void
    {
        $meter = Meter::fromArray([
            'MeterId' => 'MTR-001',
            'Position' => 'Living Room',
            'CCDValue' => 150,
            'MeterNr' => 12345,
            'ArtNr' => 11490,
            'CalcFactor' => 1.2,
            'Reduction' => 0,
            'BeginValue' => 100,
            'EndValue' => 250,
            'CValue' => 148,
            'CCValue' => 150,
            'BsDate' => '01-01-2024',
            'EsDate' => '31-12-2024',
            'serviceId' => 1,
            'BillingPeriodId' => 2024,
            'RadNr' => 3,
            'Order' => 1,
            'TransferLoss' => 5,
            'Multiply' => 2,
            'DecPos' => 0,
            'SValR' => 10,
            'EvalR' => 20,
        ]);

        $this->assertSame('MTR-001', $meter->meterId);
        $this->assertSame('Living Room', $meter->position);
        $this->assertSame(150, $meter->value);
        $this->assertSame(12345, $meter->meterNr);
        $this->assertSame(11490, $meter->artNr);
        $this->assertSame(1.2, $meter->calcFactor);
        $this->assertSame(0, $meter->reduction);
        $this->assertSame(100, $meter->beginValue);
        $this->assertSame(250, $meter->endValue);
        $this->assertSame(148, $meter->rawValue);
        $this->assertSame(150, $meter->calculatedValue);
        $this->assertSame('01-01-2024', $meter->beginDate);
        $this->assertSame('31-12-2024', $meter->endDate);
        $this->assertSame(1, $meter->serviceId);
        $this->assertSame(2024, $meter->billingPeriodId);
        $this->assertSame(3, $meter->radiatorNumber);
        $this->assertSame(1, $meter->order);
        $this->assertSame(5, $meter->transferLoss);
        $this->assertSame(2, $meter->multiply);
        $this->assertSame(0, $meter->decimalPositions);
        $this->assertSame(10, $meter->estimatedStartValue);
        $this->assertSame(20, $meter->estimatedEndValue);
    }

    public function test_to_array_returns_correct_format(): void
    {
        $meter = new Meter(
            meterId: 'MTR-001',
            position: 'Living Room',
            value: 150,
            meterNr: 12345,
            artNr: 11490,
            calcFactor: 1.2,
            reduction: 0,
            beginValue: 100,
            endValue: 250,
            rawValue: 148,
            calculatedValue: 150,
            beginDate: '01-01-2024',
            endDate: '31-12-2024',
            serviceId: 1,
            billingPeriodId: 2024,
            radiatorNumber: 3,
            order: 1,
            transferLoss: 5,
            multiply: 2,
            decimalPositions: 0,
            estimatedStartValue: 10,
            estimatedEndValue: 20,
        );

        $this->assertSame([
            'MeterId' => 'MTR-001',
            'serviceId' => 1,
            'BillingPeriodId' => 2024,
            'RadNr' => 3,
            'Order' => 1,
            'Position' => 'Living Room',
            'ArtNr' => 11490,
            'MeterNr' => 12345,
            'TransferLoss' => 5,
            'Multiply' => 2,
            'Reduction' => 0,
            'CalcFactor' => 1.2,
            'BsDate' => '01-01-2024',
            'BeginValue' => 100,
            'EsDate' => '31-12-2024',
            'EndValue' => 250,
            'CValue' => 148,
            'CCValue' => 150,
            'CCDValue' => 150,
            'DecPos' => 0,
            'SValR' => 10,
            'EvalR' => 20,
        ], $meter->toArray());
    }

    public function test_round_trip(): void
    {
        $original = [
            'MeterId' => 'MTR-002',
            'serviceId' => 1,
            'BillingPeriodId' => 2024,
            'RadNr' => 2,
            'Order' => 1,
            'Position' => 'Bedroom',
            'ArtNr' => 11490,
            'MeterNr' => 12346,
            'TransferLoss' => 0,
            'Multiply' => 1,
            'Reduction' => 0,
            'CalcFactor' => 1.0,
            'BsDate' => '01-01-2024',
            'BeginValue' => 50,
            'EsDate' => '31-12-2024',
            'EndValue' => 150,
            'CValue' => 99,
            'CCValue' => 100,
            'CCDValue' => 100,
            'DecPos' => 0,
            'SValR' => 0,
            'EvalR' => 0,
        ];

        $meter = Meter::fromArray($original);

        $this->assertSame($original, $meter->toArray());
    }

    public function test_missing_fields_use_defaults(): void
    {
        $meter = Meter::fromArray([]);

        $this->assertSame('', $meter->meterId);
        $this->assertSame('', $meter->position);
        $this->assertSame(0, $meter->value);
        $this->assertSame(0, $meter->meterNr);
        $this->assertSame(0, $meter->artNr);
        $this->assertSame(0.0, $meter->calcFactor);
        $this->assertSame(0, $meter->reduction);
        $this->assertSame(0, $meter->beginValue);
        $this->assertSame(0, $meter->endValue);
        $this->assertSame(0, $meter->rawValue);
        $this->assertSame(0, $meter->calculatedValue);
        $this->assertSame('', $meter->beginDate);
        $this->assertSame('', $meter->endDate);
        $this->assertSame(0, $meter->serviceId);
        $this->assertSame(0, $meter->billingPeriodId);
        $this->assertSame(0, $meter->radiatorNumber);
        $this->assertSame(0, $meter->order);
        $this->assertSame(0, $meter->transferLoss);
        $this->assertSame(0, $meter->multiply);
        $this->assertSame(0, $meter->decimalPositions);
        $this->assertSame(0, $meter->estimatedStartValue);
        $this->assertSame(0, $meter->estimatedEndValue);
    }

    public function test_zero_value(): void
    {
        $meter = Meter::fromArray([
            'MeterId' => 'MTR-001',
            'Position' => 'Hall',
            'CCDValue' => 0,
        ]);

        $this->assertSame(0, $meter->value);
    }
}
