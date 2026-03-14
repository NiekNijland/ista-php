<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class MonthlyServiceConsumption
{
    /**
     * @param  MonthlyDeviceConsumption[]  $deviceConsumptions
     */
    public function __construct(
        public int $serviceId,
        public int $totalConsumption,
        public int $buildingAverage,
        public bool $hasApproximation,
        public array $deviceConsumptions = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $devices */
        $devices = $data['DeviceConsumptions'] ?? [];

        return new self(
            serviceId: (int) ($data['ServiceId'] ?? 0),
            totalConsumption: (int) ($data['TotalConsumption'] ?? 0),
            buildingAverage: (int) ($data['BuldingAverage'] ?? 0),
            hasApproximation: (bool) ($data['HasApproximation'] ?? false),
            deviceConsumptions: array_map(
                static fn (array $device): MonthlyDeviceConsumption => MonthlyDeviceConsumption::fromArray($device),
                $devices,
            ),
        );
    }

    /**
     * @return array{ServiceId: int, TotalConsumption: int, BuldingAverage: int, HasApproximation: bool, DeviceConsumptions: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'ServiceId' => $this->serviceId,
            'TotalConsumption' => $this->totalConsumption,
            'BuldingAverage' => $this->buildingAverage,
            'HasApproximation' => $this->hasApproximation,
            'DeviceConsumptions' => array_values(array_map(
                static fn (MonthlyDeviceConsumption $device): array => $device->toArray(),
                $this->deviceConsumptions,
            )),
        ];
    }
}
