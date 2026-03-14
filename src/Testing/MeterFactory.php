<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\Meter;

class MeterFactory
{
    public static function make(
        string $meterId = 'MTR-001',
        string $position = 'Living Room',
        int $value = 150,
        int $meterNr = 12345,
        int $artNr = 11490,
        float $calcFactor = 1.2,
        int $reduction = 0,
        int $beginValue = 100,
        int $endValue = 250,
        int $rawValue = 148,
        int $calculatedValue = 150,
        string $beginDate = '01-01-2024',
        string $endDate = '31-12-2024',
        int $serviceId = 1,
        int $billingPeriodId = 2024,
        int $radiatorNumber = 1,
        int $order = 1,
        int $transferLoss = 0,
        int $multiply = 1,
        int $decimalPositions = 0,
        int $estimatedStartValue = 0,
        int $estimatedEndValue = 0,
    ): Meter {
        return new Meter(
            meterId: $meterId,
            position: $position,
            value: $value,
            meterNr: $meterNr,
            artNr: $artNr,
            calcFactor: $calcFactor,
            reduction: $reduction,
            beginValue: $beginValue,
            endValue: $endValue,
            rawValue: $rawValue,
            calculatedValue: $calculatedValue,
            beginDate: $beginDate,
            endDate: $endDate,
            serviceId: $serviceId,
            billingPeriodId: $billingPeriodId,
            radiatorNumber: $radiatorNumber,
            order: $order,
            transferLoss: $transferLoss,
            multiply: $multiply,
            decimalPositions: $decimalPositions,
            estimatedStartValue: $estimatedStartValue,
            estimatedEndValue: $estimatedEndValue,
        );
    }
}
