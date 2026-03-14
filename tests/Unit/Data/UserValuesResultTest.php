<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\Customer;
use NiekNijland\Ista\Data\UserValuesResult;
use PHPUnit\Framework\TestCase;

class UserValuesResultTest extends TestCase
{
    public function test_from_array_creates_result(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/user-values.json'),
            true,
        );

        $result = UserValuesResult::fromArray($data);

        $this->assertSame('John Doe', $result->displayName);
        $this->assertCount(1, $result->customers);
        $this->assertInstanceOf(Customer::class, $result->customers[0]);
        $this->assertSame('CUST-12345', $result->customers[0]->cuid);
    }

    public function test_to_array_round_trip(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/user-values.json'),
            true,
        );

        $result = UserValuesResult::fromArray($data);

        $this->assertSame($data, $result->toArray());
    }

    public function test_empty_customers(): void
    {
        $result = UserValuesResult::fromArray(['Cus' => []]);

        $this->assertSame([], $result->customers);
        $this->assertSame('', $result->displayName);
    }

    public function test_missing_cus_key(): void
    {
        $result = UserValuesResult::fromArray([]);

        $this->assertSame([], $result->customers);
        $this->assertSame('', $result->displayName);
    }
}
