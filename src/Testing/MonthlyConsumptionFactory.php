<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\MonthlyConsumption;
use NiekNijland\Ista\Data\MonthlyServiceConsumption;

class MonthlyConsumptionFactory
{
    /**
     * @param  MonthlyServiceConsumption[]|null  $serviceConsumptions
     */
    public static function make(
        int $year = 2026,
        int $month = 3,
        float $averageTemperature = 8.9,
        string $cuToken = 'token-2026-03',
        ?array $serviceConsumptions = null,
    ): MonthlyConsumption {
        return new MonthlyConsumption(
            year: $year,
            month: $month,
            averageTemperature: $averageTemperature,
            cuToken: $cuToken,
            serviceConsumptions: $serviceConsumptions ?? [MonthlyServiceConsumptionFactory::make()],
        );
    }
}
