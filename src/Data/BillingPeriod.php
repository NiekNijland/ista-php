<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class BillingPeriod
{
    public function __construct(
        public int $year,
        public string $start,
        public string $end,
        public float $totalAmount,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            year: (int) ($data['y'] ?? 0),
            start: (string) ($data['s'] ?? ''),
            end: (string) ($data['e'] ?? ''),
            totalAmount: (float) ($data['ta'] ?? 0.0),
        );
    }

    /**
     * @return array{y: int, s: string, e: string, ta: float}
     */
    public function toArray(): array
    {
        return [
            'y' => $this->year,
            's' => $this->start,
            'e' => $this->end,
            'ta' => $this->totalAmount,
        ];
    }
}
