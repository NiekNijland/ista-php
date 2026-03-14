<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class ConsumptionAverageResult
{
    /**
     * @param  list<array<string, mixed>>  $averages
     */
    public function __construct(
        public array $averages,
        public string $curStart = '',
        public string $curEnd = '',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $averages */
        $averages = $data['Averages'] ?? [];

        return new self(
            averages: $averages,
            curStart: (string) ($data['CurStart'] ?? ''),
            curEnd: (string) ($data['CurEnd'] ?? ''),
        );
    }

    /**
     * @return array{CurStart: string, CurEnd: string, Averages: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'CurStart' => $this->curStart,
            'CurEnd' => $this->curEnd,
            'Averages' => $this->averages,
        ];
    }

    /**
     * Safely retrieves the normalized building average value.
     *
     * The API typically returns the relevant value at index 1 of the Averages array,
     * but this method provides safe access instead of a hardcoded index.
     */
    public function getNormalizedValue(int $index = 1): ?int
    {
        if (! isset($this->averages[$index])) {
            return null;
        }

        $entry = $this->averages[$index];

        if (! isset($entry['NormalizedValue'])) {
            return null;
        }

        return (int) $entry['NormalizedValue'];
    }
}
