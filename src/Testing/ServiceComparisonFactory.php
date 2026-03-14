<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\Meter;
use NiekNijland\Ista\Data\ServiceComparison;

class ServiceComparisonFactory
{
    /**
     * @param  Meter[]|null  $currentMeters
     * @param  Meter[]|null  $comparisonMeters
     */
    public static function make(
        int $totalNow = 250,
        int $totalPrevious = 230,
        int $id = 1,
        int $decimalPositions = 0,
        int $totalDiffPercent = 8,
        int $totalWholePrevious = 280,
        ?array $currentMeters = null,
        ?array $comparisonMeters = null,
    ): ServiceComparison {
        return new ServiceComparison(
            totalNow: $totalNow,
            totalPrevious: $totalPrevious,
            id: $id,
            decimalPositions: $decimalPositions,
            totalDiffPercent: $totalDiffPercent,
            totalWholePrevious: $totalWholePrevious,
            currentMeters: $currentMeters ?? [
                MeterFactory::make(meterId: 'MTR-001', position: 'Living Room', value: 150),
                MeterFactory::make(meterId: 'MTR-002', position: 'Bedroom', value: 100),
            ],
            comparisonMeters: $comparisonMeters ?? [
                MeterFactory::make(meterId: 'MTR-001', position: 'Living Room', value: 140),
                MeterFactory::make(meterId: 'MTR-002', position: 'Bedroom', value: 90),
            ],
        );
    }
}
