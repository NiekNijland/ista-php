<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use DateTimeImmutable;
use NiekNijland\Ista\Data\BillingPeriod;
use NiekNijland\Ista\Data\BillingService;
use NiekNijland\Ista\Data\ConsumptionPeriod;
use NiekNijland\Ista\Data\ServiceComparison;

class ConsumptionPeriodFactory
{
    /**
     * @param  ServiceComparison[]|null  $services
     * @param  BillingService[]|null  $billingServices
     * @param  BillingPeriod[]|null  $billingPeriods
     */
    public static function make(
        ?DateTimeImmutable $start = null,
        ?DateTimeImmutable $end = null,
        ?array $services = null,
        string $compStart = '01-01-2023',
        string $compEnd = '31-12-2023',
        float $curAverageTemp = 10.5,
        float $compAverageTemp = 9.8,
        ?array $billingServices = null,
        ?array $billingPeriods = null,
    ): ConsumptionPeriod {
        return new ConsumptionPeriod(
            start: $start ?? new DateTimeImmutable('2024-01-01'),
            end: $end ?? new DateTimeImmutable('2024-12-31'),
            services: $services ?? [ServiceComparisonFactory::make()],
            compStart: $compStart,
            compEnd: $compEnd,
            curAverageTemp: $curAverageTemp,
            compAverageTemp: $compAverageTemp,
            billingServices: $billingServices ?? [BillingServiceFactory::make()],
            billingPeriods: $billingPeriods ?? [BillingPeriodFactory::make()],
        );
    }
}
