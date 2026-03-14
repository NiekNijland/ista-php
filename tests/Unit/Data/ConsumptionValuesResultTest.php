<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\ConsumptionValuesResult;
use NiekNijland\Ista\Data\ServiceComparison;
use PHPUnit\Framework\TestCase;

class ConsumptionValuesResultTest extends TestCase
{
    public function test_from_array_creates_result(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/consumption-values.json'),
            true,
        );

        $result = ConsumptionValuesResult::fromArray($data);

        $this->assertSame('01-01-2025', $result->curStart);
        $this->assertSame('31-12-2025', $result->curEnd);
        $this->assertCount(1, $result->services);
        $this->assertInstanceOf(ServiceComparison::class, $result->services[0]);
        $this->assertSame(1, $result->services[0]->id);
        $this->assertCount(1, $result->services[0]->currentMeters);
    }

    public function test_to_array_round_trip(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/consumption-values.json'),
            true,
        );

        $result = ConsumptionValuesResult::fromArray($data);

        $this->assertSame($data, $result->toArray());
    }

    public function test_empty_services(): void
    {
        $result = ConsumptionValuesResult::fromArray([
            'CurStart' => '01-01-2025',
            'CurEnd' => '31-12-2025',
            'ServicesComp' => [],
        ]);

        $this->assertSame([], $result->services);
        $this->assertSame('01-01-2025', $result->curStart);
        $this->assertSame('31-12-2025', $result->curEnd);
    }

    public function test_missing_fields_use_defaults(): void
    {
        $result = ConsumptionValuesResult::fromArray([]);

        $this->assertSame('', $result->curStart);
        $this->assertSame('', $result->curEnd);
        $this->assertSame([], $result->services);
    }
}
