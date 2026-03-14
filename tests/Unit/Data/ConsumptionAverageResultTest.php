<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Data\ConsumptionAverageResult;
use PHPUnit\Framework\TestCase;

class ConsumptionAverageResultTest extends TestCase
{
    public function test_from_array_creates_result(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/consumption-averages.json'),
            true,
        );

        $result = ConsumptionAverageResult::fromArray($data);

        $this->assertCount(2, $result->averages);
        $this->assertSame('01-01-2026', $result->curStart);
        $this->assertSame('03-07-2026', $result->curEnd);
    }

    public function test_get_normalized_value_returns_value_at_index(): void
    {
        $result = ConsumptionAverageResult::fromArray([
            'Averages' => [
                ['NormalizedValue' => 100],
                ['NormalizedValue' => 200],
            ],
        ]);

        $this->assertSame(200, $result->getNormalizedValue());
        $this->assertSame(100, $result->getNormalizedValue(0));
    }

    public function test_get_normalized_value_returns_null_for_missing_index(): void
    {
        $result = ConsumptionAverageResult::fromArray([
            'Averages' => [
                ['NormalizedValue' => 100],
            ],
        ]);

        $this->assertNull($result->getNormalizedValue());
    }

    public function test_get_normalized_value_returns_null_for_missing_key(): void
    {
        $result = ConsumptionAverageResult::fromArray([
            'Averages' => [
                ['SomeOtherKey' => 100],
                ['SomeOtherKey' => 200],
            ],
        ]);

        $this->assertNull($result->getNormalizedValue());
    }

    public function test_to_array_round_trip(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__.'/../../Fixtures/consumption-averages.json'),
            true,
        );

        $result = ConsumptionAverageResult::fromArray($data);

        $this->assertSame($data, $result->toArray());
    }

    public function test_empty_averages(): void
    {
        $result = ConsumptionAverageResult::fromArray(['Averages' => []]);

        $this->assertSame([], $result->averages);
        $this->assertNull($result->getNormalizedValue());
    }

    public function test_missing_averages_key(): void
    {
        $result = ConsumptionAverageResult::fromArray([]);

        $this->assertSame([], $result->averages);
        $this->assertSame('', $result->curStart);
        $this->assertSame('', $result->curEnd);
    }

    public function test_zero_normalized_value(): void
    {
        $result = ConsumptionAverageResult::fromArray([
            'Averages' => [
                ['NormalizedValue' => 0],
                ['NormalizedValue' => 0],
            ],
        ]);

        $this->assertSame(0, $result->getNormalizedValue());
    }

    public function test_cur_start_and_cur_end_are_preserved(): void
    {
        $result = ConsumptionAverageResult::fromArray([
            'CurStart' => '01-01-2026',
            'CurEnd' => '03-07-2026',
            'Averages' => [],
        ]);

        $this->assertSame('01-01-2026', $result->curStart);
        $this->assertSame('03-07-2026', $result->curEnd);
    }
}
