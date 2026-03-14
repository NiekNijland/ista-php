<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\MonthlyDeviceConsumption;
use NiekNijland\Ista\Data\MonthlyServiceConsumption;

class MonthlyServiceConsumptionFactory
{
    /**
     * @param  MonthlyDeviceConsumption[]|null  $deviceConsumptions
     */
    public static function make(
        int $serviceId = 1,
        int $totalConsumption = 8,
        int $buildingAverage = 36,
        bool $hasApproximation = false,
        ?array $deviceConsumptions = null,
    ): MonthlyServiceConsumption {
        return new MonthlyServiceConsumption(
            serviceId: $serviceId,
            totalConsumption: $totalConsumption,
            buildingAverage: $buildingAverage,
            hasApproximation: $hasApproximation,
            deviceConsumptions: $deviceConsumptions ?? [MonthlyDeviceConsumptionFactory::make()],
        );
    }
}
