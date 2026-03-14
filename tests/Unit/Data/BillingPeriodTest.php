<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\BillingPeriod;
use PHPUnit\Framework\TestCase;

class BillingPeriodTest extends TestCase
{
    public function test_from_array_creates_billing_period(): void
    {
        $period = BillingPeriod::fromArray([
            'y' => 2024,
            's' => '01-01-2024',
            'e' => '31-12-2024',
            'ta' => 1250.50,
        ]);

        $this->assertSame(2024, $period->year);
        $this->assertSame('01-01-2024', $period->start);
        $this->assertSame('31-12-2024', $period->end);
        $this->assertSame(1250.50, $period->totalAmount);
    }

    public function test_round_trip(): void
    {
        $data = [
            'y' => 2023,
            's' => '01-01-2023',
            'e' => '31-12-2023',
            'ta' => 1180.25,
        ];

        $period = BillingPeriod::fromArray($data);

        $this->assertSame($data, $period->toArray());
    }

    public function test_missing_fields_use_defaults(): void
    {
        $period = BillingPeriod::fromArray([]);

        $this->assertSame(0, $period->year);
        $this->assertSame('', $period->start);
        $this->assertSame('', $period->end);
        $this->assertSame(0.0, $period->totalAmount);
    }
}
