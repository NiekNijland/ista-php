<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use NiekNijland\Ista\Data\ConsumptionPeriod;
use NiekNijland\Ista\Data\Customer;

class CustomerFactory
{
    public static function make(
        string $cuid = 'CUST-12345',
        ?ConsumptionPeriod $consumption = null,
        string $address = 'Teststraat 42',
        string $zip = '1234 AB',
        string $city = 'Amsterdam',
        string $dateStart = '01-01-2020',
    ): Customer {
        return new Customer(
            cuid: $cuid,
            consumption: $consumption ?? ConsumptionPeriodFactory::make(),
            address: $address,
            zip: $zip,
            city: $city,
            dateStart: $dateStart,
        );
    }
}
