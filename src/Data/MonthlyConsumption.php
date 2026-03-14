<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class MonthlyConsumption
{
    /**
     * @param  MonthlyServiceConsumption[]  $serviceConsumptions
     */
    public function __construct(
        public int $year,
        public int $month,
        public float $averageTemperature,
        public string $cuToken,
        public array $serviceConsumptions = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $services */
        $services = $data['ServiceConsumptions'] ?? [];

        return new self(
            year: (int) ($data['y'] ?? 0),
            month: (int) ($data['m'] ?? 0),
            averageTemperature: (float) ($data['at'] ?? 0.0),
            cuToken: (string) ($data['CuToken'] ?? ''),
            serviceConsumptions: array_map(
                static fn (array $service): MonthlyServiceConsumption => MonthlyServiceConsumption::fromArray($service),
                $services,
            ),
        );
    }

    /**
     * @return array{y: int, m: int, at: float, CuToken: string, ServiceConsumptions: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'y' => $this->year,
            'm' => $this->month,
            'at' => $this->averageTemperature,
            'CuToken' => $this->cuToken,
            'ServiceConsumptions' => array_values(array_map(
                static fn (MonthlyServiceConsumption $service): array => $service->toArray(),
                $this->serviceConsumptions,
            )),
        ];
    }
}
