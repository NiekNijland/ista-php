<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\ConsumptionAverageResult;

class ConsumptionAverageResultFactory
{
    /**
     * @param  list<array<string, mixed>>|null  $averages
     */
    public static function make(
        ?array $averages = null,
        string $curStart = '01-01-2024',
        string $curEnd = '31-12-2024',
    ): ConsumptionAverageResult {
        return new ConsumptionAverageResult(
            averages: $averages ?? [
                ['BillingServiceId' => 901, 'NormalizedValue' => 116.732, 'DecPos' => 3],
                ['BillingServiceId' => 1, 'NormalizedValue' => 915, 'DecPos' => 0],
            ],
            curStart: $curStart,
            curEnd: $curEnd,
        );
    }
}
