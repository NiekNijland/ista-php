<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class MonthlyConsumptionResult
{
    /**
     * @param  int[]  $years
     * @param  MonthlyConsumption[]  $months
     */
    public function __construct(
        public string $cuid,
        public string $startDate,
        public string $endDate,
        public int $showMonths,
        public int $hasMonths,
        public array $years,
        public array $months,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<int> $years */
        $years = $data['ys'] ?? [];

        /** @var list<array<string, mixed>> $months */
        $months = $data['mc'] ?? [];

        return new self(
            cuid: (string) ($data['cuid'] ?? ''),
            startDate: (string) ($data['sd'] ?? ''),
            endDate: (string) ($data['ed'] ?? ''),
            showMonths: (int) ($data['sh'] ?? 0),
            hasMonths: (int) ($data['hs'] ?? 0),
            years: array_map(static fn (mixed $y): int => $y, $years),
            months: array_map(
                static fn (array $month): MonthlyConsumption => MonthlyConsumption::fromArray($month),
                $months,
            ),
        );
    }

    /**
     * @return array{cuid: string, sd: string, ed: string, sh: int, hs: int, ys: list<int>, mc: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'cuid' => $this->cuid,
            'sd' => $this->startDate,
            'ed' => $this->endDate,
            'sh' => $this->showMonths,
            'hs' => $this->hasMonths,
            'ys' => array_values($this->years),
            'mc' => array_values(array_map(
                static fn (MonthlyConsumption $month): array => $month->toArray(),
                $this->months,
            )),
        ];
    }
}
