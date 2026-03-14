<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class ServiceComparison
{
    /**
     * @param  Meter[]  $currentMeters
     * @param  Meter[]  $comparisonMeters
     */
    public function __construct(
        public int $totalNow,
        public int $totalPrevious,
        public int $id = 0,
        public int $decimalPositions = 0,
        public int $totalDiffPercent = 0,
        public int $totalWholePrevious = 0,
        public array $currentMeters = [],
        public array $comparisonMeters = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $curMeters */
        $curMeters = $data['CurMeters'] ?? [];

        /** @var list<array<string, mixed>> $compMeters */
        $compMeters = $data['CompMeters'] ?? [];

        return new self(
            totalNow: (int) ($data['TotalNow'] ?? 0),
            totalPrevious: (int) ($data['TotalPrevious'] ?? 0),
            id: (int) ($data['Id'] ?? 0),
            decimalPositions: (int) ($data['DecPos'] ?? 0),
            totalDiffPercent: (int) ($data['TotalDiffperc'] ?? 0),
            totalWholePrevious: (int) ($data['TotalWholePrevious'] ?? 0),
            currentMeters: array_map(static fn (array $meter): Meter => Meter::fromArray($meter), $curMeters),
            comparisonMeters: array_map(static fn (array $meter): Meter => Meter::fromArray($meter), $compMeters),
        );
    }

    /**
     * @return array{Id: int, DecPos: int, TotalNow: int, TotalPrevious: int, TotalDiffperc: int, TotalWholePrevious: int, CurMeters: list<array<string, mixed>>, CompMeters: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'Id' => $this->id,
            'DecPos' => $this->decimalPositions,
            'TotalNow' => $this->totalNow,
            'TotalPrevious' => $this->totalPrevious,
            'TotalDiffperc' => $this->totalDiffPercent,
            'TotalWholePrevious' => $this->totalWholePrevious,
            'CurMeters' => array_values(array_map(static fn (Meter $meter): array => $meter->toArray(), $this->currentMeters)),
            'CompMeters' => array_values(array_map(static fn (Meter $meter): array => $meter->toArray(), $this->comparisonMeters)),
        ];
    }
}
