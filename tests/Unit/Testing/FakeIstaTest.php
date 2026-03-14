<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Testing;

use DateTimeImmutable;
use NiekNijland\Ista\Exception\IstaException;
use NiekNijland\Ista\Testing\ConsumptionAverageResultFactory;
use NiekNijland\Ista\Testing\ConsumptionValuesResultFactory;
use NiekNijland\Ista\Testing\FakeIsta;
use NiekNijland\Ista\Testing\MonthlyConsumptionResultFactory;
use NiekNijland\Ista\Testing\UserValuesResultFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FakeIstaTest extends TestCase
{
    public function test_get_user_values_returns_seeded_result(): void
    {
        $fake = new FakeIsta;
        $expected = UserValuesResultFactory::make();
        $fake->seedUserValuesResult($expected);

        $result = $fake->getUserValues();

        $this->assertSame($expected, $result);
    }

    public function test_get_consumption_averages_returns_seeded_result(): void
    {
        $fake = new FakeIsta;
        $expected = ConsumptionAverageResultFactory::make();
        $fake->seedConsumptionAverageResult($expected);

        $result = $fake->getConsumptionAverages('CUST-123', new DateTimeImmutable, new DateTimeImmutable);

        $this->assertSame($expected, $result);
    }

    public function test_should_throw_causes_exception(): void
    {
        $fake = new FakeIsta;
        $fake->shouldThrow(new IstaException('Test error'));

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('Test error');

        $fake->getUserValues();
    }

    public function test_unseeded_get_user_values_throws(): void
    {
        $fake = new FakeIsta;

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('No UserValuesResult seeded');

        $fake->getUserValues();
    }

    public function test_unseeded_get_consumption_averages_throws(): void
    {
        $fake = new FakeIsta;

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('No ConsumptionAverageResult seeded');

        $fake->getConsumptionAverages('CUST-123', new DateTimeImmutable, new DateTimeImmutable);
    }

    public function test_assert_called(): void
    {
        $fake = new FakeIsta;
        $fake->seedUserValuesResult(UserValuesResultFactory::make());
        $fake->getUserValues();

        $fake->assertCalled('getUserValues');
        $this->addToAssertionCount(1);
    }

    public function test_assert_called_fails_when_not_called(): void
    {
        $fake = new FakeIsta;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected method [getUserValues] to have been called');

        $fake->assertCalled('getUserValues');
    }

    public function test_assert_not_called(): void
    {
        $fake = new FakeIsta;

        $fake->assertNotCalled('getUserValues');
        $this->addToAssertionCount(1);
    }

    public function test_assert_not_called_fails_when_called(): void
    {
        $fake = new FakeIsta;
        $fake->seedUserValuesResult(UserValuesResultFactory::make());
        $fake->getUserValues();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected method [getUserValues] to not have been called');

        $fake->assertNotCalled('getUserValues');
    }

    public function test_assert_called_times(): void
    {
        $fake = new FakeIsta;
        $fake->seedUserValuesResult(UserValuesResultFactory::make());
        $fake->seedUserValuesResult(UserValuesResultFactory::make());
        $fake->getUserValues();
        $fake->getUserValues();

        $fake->assertCalledTimes('getUserValues', 2);
        $this->addToAssertionCount(1);
    }

    public function test_assert_called_times_fails_with_wrong_count(): void
    {
        $fake = new FakeIsta;
        $fake->seedUserValuesResult(UserValuesResultFactory::make());
        $fake->getUserValues();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('called 1 time(s)');

        $fake->assertCalledTimes('getUserValues', 3);
    }

    public function test_recorded_calls_track_arguments(): void
    {
        $fake = new FakeIsta;
        $fake->seedConsumptionAverageResult(ConsumptionAverageResultFactory::make());

        $start = new DateTimeImmutable('2024-01-01');
        $end = new DateTimeImmutable('2024-12-31');
        $fake->getConsumptionAverages('CUST-123', $start, $end);

        $calls = $fake->getRecordedCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('getConsumptionAverages', $calls[0]->method);
        $this->assertSame('CUST-123', $calls[0]->arguments[0]);
        $this->assertSame($start, $calls[0]->arguments[1]);
        $this->assertSame($end, $calls[0]->arguments[2]);
    }

    public function test_get_monthly_consumption_returns_seeded_result(): void
    {
        $fake = new FakeIsta;
        $expected = MonthlyConsumptionResultFactory::make();
        $fake->seedMonthlyConsumptionResult($expected);

        $result = $fake->getMonthlyConsumption('CUST-12345');

        $this->assertSame($expected, $result);
    }

    public function test_unseeded_get_monthly_consumption_throws(): void
    {
        $fake = new FakeIsta;

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('No MonthlyConsumptionResult seeded');

        $fake->getMonthlyConsumption('CUST-12345');
    }

    public function test_recorded_calls_track_monthly_consumption_arguments(): void
    {
        $fake = new FakeIsta;
        $fake->seedMonthlyConsumptionResult(MonthlyConsumptionResultFactory::make());
        $fake->getMonthlyConsumption('CUST-12345');

        $calls = $fake->getRecordedCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('getMonthlyConsumption', $calls[0]->method);
        $this->assertSame('CUST-12345', $calls[0]->arguments[0]);
    }

    public function test_get_consumption_values_returns_seeded_result(): void
    {
        $fake = new FakeIsta;
        $expected = ConsumptionValuesResultFactory::make();
        $fake->seedConsumptionValuesResult($expected);

        $result = $fake->getConsumptionValues('CUST-12345', 2025, '2025-01-01T00:00:00', '2025-12-31T00:00:00');

        $this->assertSame($expected, $result);
    }

    public function test_unseeded_get_consumption_values_throws(): void
    {
        $fake = new FakeIsta;

        $this->expectException(IstaException::class);
        $this->expectExceptionMessage('No ConsumptionValuesResult seeded');

        $fake->getConsumptionValues('CUST-12345', 2025, '2025-01-01T00:00:00', '2025-12-31T00:00:00');
    }

    public function test_recorded_calls_track_consumption_values_arguments(): void
    {
        $fake = new FakeIsta;
        $fake->seedConsumptionValuesResult(ConsumptionValuesResultFactory::make());
        $fake->getConsumptionValues('CUST-12345', 2025, '2025-01-01T00:00:00', '2025-12-31T00:00:00');

        $calls = $fake->getRecordedCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('getConsumptionValues', $calls[0]->method);
        $this->assertSame('CUST-12345', $calls[0]->arguments[0]);
        $this->assertSame(2025, $calls[0]->arguments[1]);
        $this->assertSame('2025-01-01T00:00:00', $calls[0]->arguments[2]);
        $this->assertSame('2025-12-31T00:00:00', $calls[0]->arguments[3]);
    }
}
