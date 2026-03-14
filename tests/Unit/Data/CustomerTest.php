<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\Customer;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function test_from_array_creates_customer(): void
    {
        $data = [
            'Cuid' => 'CUST-12345',
            'Adress' => 'Teststraat 42',
            'Zip' => '1234 AB',
            'City' => 'Amsterdam',
            'DateStart' => '01-01-2020',
            'curConsumption' => [
                'CurStart' => '01-01-2024',
                'CurEnd' => '31-12-2024',
                'ServicesComp' => [],
            ],
        ];

        $customer = Customer::fromArray($data);

        $this->assertSame('CUST-12345', $customer->cuid);
        $this->assertSame('Teststraat 42', $customer->address);
        $this->assertSame('1234 AB', $customer->zip);
        $this->assertSame('Amsterdam', $customer->city);
        $this->assertSame('01-01-2020', $customer->dateStart);
        $this->assertSame('2024-01-01', $customer->consumption->start->format('Y-m-d'));
        $this->assertSame('2024-12-31', $customer->consumption->end->format('Y-m-d'));
    }

    public function test_to_array_returns_correct_format(): void
    {
        $data = [
            'Cuid' => 'CUST-12345',
            'Adress' => 'Teststraat 42',
            'Zip' => '1234 AB',
            'City' => 'Amsterdam',
            'DateStart' => '01-01-2020',
            'curConsumption' => [
                'CurStart' => '01-01-2024',
                'CurEnd' => '31-12-2024',
                'CompStart' => '',
                'CompEnd' => '',
                'CurAverageTemp' => 0.0,
                'CompAverageTemp' => 0.0,
                'Billingservices' => [],
                'BillingPeriods' => [],
                'ServicesComp' => [],
            ],
        ];

        $customer = Customer::fromArray($data);
        $array = $customer->toArray();

        $this->assertSame('CUST-12345', $array['Cuid']);
        $this->assertSame('Teststraat 42', $array['Adress']);
        $this->assertSame('1234 AB', $array['Zip']);
        $this->assertSame('Amsterdam', $array['City']);
        $this->assertSame('01-01-2020', $array['DateStart']);
        $this->assertSame('01-01-2024', $array['curConsumption']['CurStart']);
    }

    public function test_round_trip(): void
    {
        $data = [
            'Cuid' => 'CUST-12345',
            'Adress' => 'Teststraat 42',
            'Zip' => '1234 AB',
            'City' => 'Amsterdam',
            'DateStart' => '01-01-2020',
            'curConsumption' => [
                'CurStart' => '01-01-2024',
                'CurEnd' => '31-12-2024',
                'CompStart' => '',
                'CompEnd' => '',
                'CurAverageTemp' => 0.0,
                'CompAverageTemp' => 0.0,
                'Billingservices' => [],
                'BillingPeriods' => [],
                'ServicesComp' => [],
            ],
        ];

        $customer = Customer::fromArray($data);

        $this->assertSame($data, $customer->toArray());
    }

    public function test_missing_fields_use_defaults(): void
    {
        $data = [
            'curConsumption' => [
                'CurStart' => '01-01-2024',
                'CurEnd' => '31-12-2024',
                'ServicesComp' => [],
            ],
        ];

        $customer = Customer::fromArray($data);

        $this->assertSame('', $customer->cuid);
        $this->assertSame('', $customer->address);
        $this->assertSame('', $customer->zip);
        $this->assertSame('', $customer->city);
        $this->assertSame('', $customer->dateStart);
    }
}
