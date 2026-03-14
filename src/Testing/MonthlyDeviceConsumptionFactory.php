<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\MonthlyDeviceConsumption;

class MonthlyDeviceConsumptionFactory
{
    public static function make(
        int $id = 1789316,
        int $output = 1,
        int $artNr = 11490,
        int $serialNr = 918564311,
        string $startDate = '2026-03-01T00:00:00',
        float $startValue = 0.0,
        string $endDate = '2026-03-07T00:00:00',
        float $endValue = 8.0,
        float $rawValue = 8.0,
        float $calculatedValue = 8.0,
        int $value = 8,
        string $progJump = '9998-12-31T00:00:00',
        string $active = '2020-03-02T00:00:00',
        string $roomDescription = 'Hal',
    ): MonthlyDeviceConsumption {
        return new MonthlyDeviceConsumption(
            id: $id,
            output: $output,
            artNr: $artNr,
            serialNr: $serialNr,
            startDate: $startDate,
            startValue: $startValue,
            endDate: $endDate,
            endValue: $endValue,
            rawValue: $rawValue,
            calculatedValue: $calculatedValue,
            value: $value,
            progJump: $progJump,
            active: $active,
            roomDescription: $roomDescription,
        );
    }
}
