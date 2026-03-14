<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\BillingService;

class BillingServiceFactory
{
    public static function make(
        int $id = 1,
        string $description = 'Heating',
        string $meterType = 'HKV',
        string $unit = 'Units',
    ): BillingService {
        return new BillingService(
            id: $id,
            description: $description,
            meterType: $meterType,
            unit: $unit,
        );
    }
}
