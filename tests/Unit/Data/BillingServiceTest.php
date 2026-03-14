<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\BillingService;
use PHPUnit\Framework\TestCase;

class BillingServiceTest extends TestCase
{
    public function test_from_array_creates_billing_service(): void
    {
        $service = BillingService::fromArray([
            'Id' => 1,
            'Description' => 'Heating',
            'MeterType' => 'HKV',
            'Unit' => 'Units',
        ]);

        $this->assertSame(1, $service->id);
        $this->assertSame('Heating', $service->description);
        $this->assertSame('HKV', $service->meterType);
        $this->assertSame('Units', $service->unit);
    }

    public function test_round_trip(): void
    {
        $data = [
            'Id' => 2,
            'Description' => 'Hot Water',
            'MeterType' => 'WWM',
            'Unit' => 'm3',
        ];

        $service = BillingService::fromArray($data);

        $this->assertSame($data, $service->toArray());
    }

    public function test_missing_fields_use_defaults(): void
    {
        $service = BillingService::fromArray([]);

        $this->assertSame(0, $service->id);
        $this->assertSame('', $service->description);
        $this->assertSame('', $service->meterType);
        $this->assertSame('', $service->unit);
    }
}
