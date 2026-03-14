<?php

declare(strict_types=1);

namespace NiekNijland\Ista;

use DateTimeImmutable;
use NiekNijland\Ista\Data\ConsumptionAverageResult;
use NiekNijland\Ista\Data\ConsumptionValuesResult;
use NiekNijland\Ista\Data\MonthlyConsumptionResult;
use NiekNijland\Ista\Data\UserValuesResult;
use NiekNijland\Ista\Exception\IstaException;

interface IstaInterface
{
    /**
     * @throws IstaException
     */
    public function getUserValues(): UserValuesResult;

    /**
     * @throws IstaException
     */
    public function getConsumptionAverages(string $cuid, DateTimeImmutable $start, DateTimeImmutable $end): ConsumptionAverageResult;

    /**
     * @throws IstaException
     */
    public function getMonthlyConsumption(string $cuid): MonthlyConsumptionResult;

    /**
     * @throws IstaException
     */
    public function getConsumptionValues(string $cuid, int $year, string $start, string $end): ConsumptionValuesResult;
}
