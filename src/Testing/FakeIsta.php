<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

use DateTimeImmutable;
use NiekNijland\Ista\Data\ConsumptionAverageResult;
use NiekNijland\Ista\Data\ConsumptionValuesResult;
use NiekNijland\Ista\Data\MonthlyConsumptionResult;
use NiekNijland\Ista\Data\UserValuesResult;
use NiekNijland\Ista\Exception\IstaException;
use NiekNijland\Ista\IstaInterface;
use RuntimeException;

class FakeIsta implements IstaInterface
{
    /** @var RecordedCall[] */
    private array $recordedCalls = [];

    /** @var UserValuesResult[] */
    private array $userValuesResults = [];

    /** @var ConsumptionAverageResult[] */
    private array $consumptionAverageResults = [];

    /** @var MonthlyConsumptionResult[] */
    private array $monthlyConsumptionResults = [];

    /** @var ConsumptionValuesResult[] */
    private array $consumptionValuesResults = [];

    private ?IstaException $exception = null;

    public function seedUserValuesResult(UserValuesResult $result): self
    {
        $this->userValuesResults[] = $result;

        return $this;
    }

    public function seedConsumptionAverageResult(ConsumptionAverageResult $result): self
    {
        $this->consumptionAverageResults[] = $result;

        return $this;
    }

    public function seedMonthlyConsumptionResult(MonthlyConsumptionResult $result): self
    {
        $this->monthlyConsumptionResults[] = $result;

        return $this;
    }

    public function seedConsumptionValuesResult(ConsumptionValuesResult $result): self
    {
        $this->consumptionValuesResults[] = $result;

        return $this;
    }

    public function shouldThrow(IstaException $exception): self
    {
        $this->exception = $exception;

        return $this;
    }

    public function getUserValues(): UserValuesResult
    {
        $this->recordedCalls[] = new RecordedCall('getUserValues', []);

        if ($this->exception instanceof IstaException) {
            throw $this->exception;
        }

        if ($this->userValuesResults === []) {
            throw new IstaException('No UserValuesResult seeded in FakeIsta');
        }

        return array_shift($this->userValuesResults);
    }

    public function getConsumptionAverages(string $cuid, DateTimeImmutable $start, DateTimeImmutable $end): ConsumptionAverageResult
    {
        $this->recordedCalls[] = new RecordedCall('getConsumptionAverages', [$cuid, $start, $end]);

        if ($this->exception instanceof IstaException) {
            throw $this->exception;
        }

        if ($this->consumptionAverageResults === []) {
            throw new IstaException('No ConsumptionAverageResult seeded in FakeIsta');
        }

        return array_shift($this->consumptionAverageResults);
    }

    public function getMonthlyConsumption(string $cuid): MonthlyConsumptionResult
    {
        $this->recordedCalls[] = new RecordedCall('getMonthlyConsumption', [$cuid]);

        if ($this->exception instanceof IstaException) {
            throw $this->exception;
        }

        if ($this->monthlyConsumptionResults === []) {
            throw new IstaException('No MonthlyConsumptionResult seeded in FakeIsta');
        }

        return array_shift($this->monthlyConsumptionResults);
    }

    public function getConsumptionValues(string $cuid, int $year, string $start, string $end): ConsumptionValuesResult
    {
        $this->recordedCalls[] = new RecordedCall('getConsumptionValues', [$cuid, $year, $start, $end]);

        if ($this->exception instanceof IstaException) {
            throw $this->exception;
        }

        if ($this->consumptionValuesResults === []) {
            throw new IstaException('No ConsumptionValuesResult seeded in FakeIsta');
        }

        return array_shift($this->consumptionValuesResults);
    }

    /**
     * @return RecordedCall[]
     */
    public function getRecordedCalls(): array
    {
        return $this->recordedCalls;
    }

    public function assertCalled(string $method): void
    {
        if ($this->countCalls($method) < 1) {
            throw new RuntimeException("Expected method [{$method}] to have been called, but it was not.");
        }
    }

    public function assertNotCalled(string $method): void
    {
        if ($this->countCalls($method) > 0) {
            throw new RuntimeException("Expected method [{$method}] to not have been called, but it was.");
        }
    }

    public function assertCalledTimes(string $method, int $times): void
    {
        $actual = $this->countCalls($method);

        if ($actual !== $times) {
            throw new RuntimeException(
                "Expected method [{$method}] to have been called {$times} time(s), but it was called {$actual} time(s).",
            );
        }
    }

    private function countCalls(string $method): int
    {
        return count(array_filter(
            $this->recordedCalls,
            static fn (RecordedCall $call): bool => $call->method === $method,
        ));
    }
}
