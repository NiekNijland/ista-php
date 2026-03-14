<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\Meter;
use NiekNijland\Ista\Data\ServiceComparison;
use PHPUnit\Framework\TestCase;

class ServiceComparisonTest extends TestCase
{
    public function test_from_array_creates_service_comparison(): void
    {
        $data = [
            'Id' => 1,
            'DecPos' => 0,
            'TotalNow' => 250,
            'TotalPrevious' => 230,
            'TotalDiffperc' => 8,
            'TotalWholePrevious' => 280,
            'CurMeters' => [
                ['MeterId' => 'MTR-001', 'Position' => 'Living Room', 'CCDValue' => 150],
                ['MeterId' => 'MTR-002', 'Position' => 'Bedroom', 'CCDValue' => 100],
            ],
            'CompMeters' => [
                ['MeterId' => 'MTR-001', 'Position' => 'Living Room', 'CCDValue' => 140],
            ],
        ];

        $service = ServiceComparison::fromArray($data);

        $this->assertSame(1, $service->id);
        $this->assertSame(0, $service->decimalPositions);
        $this->assertSame(250, $service->totalNow);
        $this->assertSame(230, $service->totalPrevious);
        $this->assertSame(8, $service->totalDiffPercent);
        $this->assertSame(280, $service->totalWholePrevious);
        $this->assertCount(2, $service->currentMeters);
        $this->assertCount(1, $service->comparisonMeters);
        $this->assertInstanceOf(Meter::class, $service->currentMeters[0]);
        $this->assertSame('MTR-001', $service->currentMeters[0]->meterId);
    }

    public function test_to_array_returns_correct_format(): void
    {
        $service = new ServiceComparison(
            totalNow: 250,
            totalPrevious: 230,
            id: 1,
            decimalPositions: 0,
            totalDiffPercent: 8,
            totalWholePrevious: 280,
            currentMeters: [new Meter('MTR-001', 'Living Room', 150)],
            comparisonMeters: [new Meter('MTR-001', 'Living Room', 140)],
        );

        $array = $service->toArray();

        $this->assertSame(1, $array['Id']);
        $this->assertSame(0, $array['DecPos']);
        $this->assertSame(250, $array['TotalNow']);
        $this->assertSame(230, $array['TotalPrevious']);
        $this->assertSame(8, $array['TotalDiffperc']);
        $this->assertSame(280, $array['TotalWholePrevious']);
        $this->assertCount(1, $array['CurMeters']);
        $this->assertCount(1, $array['CompMeters']);
    }

    public function test_round_trip(): void
    {
        $data = [
            'Id' => 1,
            'DecPos' => 0,
            'TotalNow' => 250,
            'TotalPrevious' => 230,
            'TotalDiffperc' => 8,
            'TotalWholePrevious' => 280,
            'CurMeters' => [
                ['MeterId' => 'MTR-001', 'serviceId' => 0, 'BillingPeriodId' => 0, 'RadNr' => 0, 'Order' => 0, 'Position' => 'Living Room', 'ArtNr' => 0, 'MeterNr' => 0, 'TransferLoss' => 0, 'Multiply' => 0, 'Reduction' => 0, 'CalcFactor' => 0.0, 'BsDate' => '', 'BeginValue' => 0, 'EsDate' => '', 'EndValue' => 0, 'CValue' => 0, 'CCValue' => 0, 'CCDValue' => 150, 'DecPos' => 0, 'SValR' => 0, 'EvalR' => 0],
            ],
            'CompMeters' => [
                ['MeterId' => 'MTR-001', 'serviceId' => 0, 'BillingPeriodId' => 0, 'RadNr' => 0, 'Order' => 0, 'Position' => 'Living Room', 'ArtNr' => 0, 'MeterNr' => 0, 'TransferLoss' => 0, 'Multiply' => 0, 'Reduction' => 0, 'CalcFactor' => 0.0, 'BsDate' => '', 'BeginValue' => 0, 'EsDate' => '', 'EndValue' => 0, 'CValue' => 0, 'CCValue' => 0, 'CCDValue' => 140, 'DecPos' => 0, 'SValR' => 0, 'EvalR' => 0],
            ],
        ];

        $service = ServiceComparison::fromArray($data);

        $this->assertSame($data, $service->toArray());
    }

    public function test_empty_meters(): void
    {
        $service = ServiceComparison::fromArray([
            'TotalNow' => 0,
            'TotalPrevious' => 0,
            'CurMeters' => [],
            'CompMeters' => [],
        ]);

        $this->assertSame([], $service->currentMeters);
        $this->assertSame([], $service->comparisonMeters);
    }

    public function test_missing_fields_use_defaults(): void
    {
        $service = ServiceComparison::fromArray([]);

        $this->assertSame(0, $service->id);
        $this->assertSame(0, $service->decimalPositions);
        $this->assertSame(0, $service->totalNow);
        $this->assertSame(0, $service->totalPrevious);
        $this->assertSame(0, $service->totalDiffPercent);
        $this->assertSame(0, $service->totalWholePrevious);
        $this->assertSame([], $service->currentMeters);
        $this->assertSame([], $service->comparisonMeters);
    }
}
