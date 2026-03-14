<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\Customer;
use NiekNijland\Ista\Data\UserValuesResult;

class UserValuesResultFactory
{
    /**
     * @param  Customer[]|null  $customers
     */
    public static function make(
        ?array $customers = null,
        string $displayName = 'John Doe',
    ): UserValuesResult {
        return new UserValuesResult(
            customers: $customers ?? [CustomerFactory::make()],
            displayName: $displayName,
        );
    }
}
