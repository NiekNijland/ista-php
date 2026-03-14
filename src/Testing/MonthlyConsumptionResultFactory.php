<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\MonthlyConsumption;
use NiekNijland\Ista\Data\MonthlyConsumptionResult;

class MonthlyConsumptionResultFactory
{
    /**
     * @param  int[]|null  $years
     * @param  MonthlyConsumption[]|null  $months
     */
    public static function make(
        string $cuid = 'CUST-12345',
        string $startDate = '2023-04-26T00:00:00',
        string $endDate = '2026-03-07T00:00:00',
        int $showMonths = 36,
        int $hasMonths = 36,
        ?array $years = null,
        ?array $months = null,
    ): MonthlyConsumptionResult {
        return new MonthlyConsumptionResult(
            cuid: $cuid,
            startDate: $startDate,
            endDate: $endDate,
            showMonths: $showMonths,
            hasMonths: $hasMonths,
            years: $years ?? [2026, 2025, 2024],
            months: $months ?? [MonthlyConsumptionFactory::make()],
        );
    }
}
