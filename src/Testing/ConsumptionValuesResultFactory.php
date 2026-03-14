<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\ConsumptionValuesResult;
use NiekNijland\Ista\Data\ServiceComparison;

class ConsumptionValuesResultFactory
{
    /**
     * @param  ServiceComparison[]|null  $services
     */
    public static function make(
        string $curStart = '01-01-2025',
        string $curEnd = '31-12-2025',
        ?array $services = null,
    ): ConsumptionValuesResult {
        return new ConsumptionValuesResult(
            curStart: $curStart,
            curEnd: $curEnd,
            services: $services ?? [ServiceComparisonFactory::make()],
        );
    }
}
