<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\BillingPeriod;

class BillingPeriodFactory
{
    public static function make(
        int $year = 2024,
        string $start = '01-01-2024',
        string $end = '31-12-2024',
        float $totalAmount = 1250.50,
    ): BillingPeriod {
        return new BillingPeriod(
            year: $year,
            start: $start,
            end: $end,
            totalAmount: $totalAmount,
        );
    }
}
