<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\BillingPeriod;
use NiekNijland\Ista\Data\BillingService;
use NiekNijland\Ista\Data\ConsumptionPeriod;
use NiekNijland\Ista\Data\ServiceComparison;
use NiekNijland\Ista\Exception\IstaException;
use PHPUnit\Framework\TestCase;

class ConsumptionPeriodTest extends TestCase
{
    public function test_from_array_creates_consumption_period(): void
    {
        $data = [
            'CurStart' => '01-01-2024',
            'CurEnd' => '31-12-2024',
            'CompStart' => '01-01-2023',
            'CompEnd' => '31-12-2023',
            'CurAverageTemp' => 10.5,
            'CompAverageTemp' => 9.8,
            'Billingservices' => [
                ['Id' => 1, 'Description' => 'Heating', 'MeterType' => 'HKV', 'Unit' => 'Units'],
            ],
            'BillingPeriods' => [
                ['y' => 2024, 's' => '01-01-2024', 'e' => '31-12-2024', 'ta' => 1250.50],
            ],
            'ServicesComp' => [
                [
                    'TotalNow' => 250,
                    'TotalPrevious' => 230,
                    'CurMeters' => [],
                    'CompMeters' => [],
                ],
            ],
        ];

        $period = ConsumptionPeriod::fromArray($data);

        $this->assertSame('2024-01-01', $period->start->format('Y-m-d'));
        $this->assertSame('2024-12-31', $period->end->format('Y-m-d'));
        $this->assertSame('01-01-2023', $period->compStart);
        $this->assertSame('31-12-2023', $period->compEnd);
        $this->assertSame(10.5, $period->curAverageTemp);
        $this->assertSame(9.8, $period->compAverageTemp);
        $this->assertCount(1, $period->billingServices);
        $this->assertInstanceOf(BillingService::class, $period->billingServices[0]);
        $this->assertCount(1, $period->billingPeriods);
        $this->assertInstanceOf(BillingPeriod::class, $period->billingPeriods[0]);
        $this->assertCount(1, $period->services);
        $this->assertInstanceOf(ServiceComparison::class, $period->services[0]);
    }

    public function test_to_array_returns_correct_date_format(): void
    {
        $data = [
            'CurStart' => '15-06-2024',
            'CurEnd' => '14-06-2025',
            'ServicesComp' => [],
        ];

        $period = ConsumptionPeriod::fromArray($data);
        $array = $period->toArray();

        $this->assertSame('15-06-2024', $array['CurStart']);
        $this->assertSame('14-06-2025', $array['CurEnd']);
    }

    public function test_round_trip(): void
    {
        $data = [
            'CurStart' => '01-01-2024',
            'CurEnd' => '31-12-2024',
            'CompStart' => '01-01-2023',
            'CompEnd' => '31-12-2023',
            'CurAverageTemp' => 10.5,
            'CompAverageTemp' => 9.8,
            'Billingservices' => [
                ['Id' => 1, 'Description' => 'Heating', 'MeterType' => 'HKV', 'Unit' => 'Units'],
            ],
            'BillingPeriods' => [
                ['y' => 2024, 's' => '01-01-2024', 'e' => '31-12-2024', 'ta' => 1250.50],
            ],
            'ServicesComp' => [
                [
                    'Id' => 0,
                    'DecPos' => 0,
                    'TotalNow' => 250,
                    'TotalPrevious' => 230,
                    'TotalDiffperc' => 0,
                    'TotalWholePrevious' => 0,
                    'CurMeters' => [
                        ['MeterId' => 'MTR-001', 'serviceId' => 0, 'BillingPeriodId' => 0, 'RadNr' => 0, 'Order' => 0, 'Position' => 'Living Room', 'ArtNr' => 0, 'MeterNr' => 0, 'TransferLoss' => 0, 'Multiply' => 0, 'Reduction' => 0, 'CalcFactor' => 0.0, 'BsDate' => '', 'BeginValue' => 0, 'EsDate' => '', 'EndValue' => 0, 'CValue' => 0, 'CCValue' => 0, 'CCDValue' => 150, 'DecPos' => 0, 'SValR' => 0, 'EvalR' => 0],
                    ],
                    'CompMeters' => [
                        ['MeterId' => 'MTR-001', 'serviceId' => 0, 'BillingPeriodId' => 0, 'RadNr' => 0, 'Order' => 0, 'Position' => 'Living Room', 'ArtNr' => 0, 'MeterNr' => 0, 'TransferLoss' => 0, 'Multiply' => 0, 'Reduction' => 0, 'CalcFactor' => 0.0, 'BsDate' => '', 'BeginValue' => 0, 'EsDate' => '', 'EndValue' => 0, 'CValue' => 0, 'CCValue' => 0, 'CCDValue' => 140, 'DecPos' => 0, 'SValR' => 0, 'EvalR' => 0],
                    ],
                ],
            ],
        ];

        $period = ConsumptionPeriod::fromArray($data);

        $this->assertSame($data, $period->toArray());
    }

    public function test_malformed_date_throws_exception(): void
    {
        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('Failed to parse date');

        ConsumptionPeriod::fromArray([
            'CurStart' => 'not-a-date',
            'CurEnd' => '31-12-2024',
            'ServicesComp' => [],
        ]);
    }

    public function test_empty_date_throws_exception(): void
    {
        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('Failed to parse date');

        ConsumptionPeriod::fromArray([
            'CurStart' => '',
            'CurEnd' => '31-12-2024',
            'ServicesComp' => [],
        ]);
    }

    public function test_empty_services(): void
    {
        $period = ConsumptionPeriod::fromArray([
            'CurStart' => '01-01-2024',
            'CurEnd' => '31-12-2024',
            'ServicesComp' => [],
        ]);

        $this->assertSame([], $period->services);
        $this->assertSame([], $period->billingServices);
        $this->assertSame([], $period->billingPeriods);
        $this->assertSame('', $period->compStart);
        $this->assertSame('', $period->compEnd);
        $this->assertSame(0.0, $period->curAverageTemp);
        $this->assertSame(0.0, $period->compAverageTemp);
    }
}
