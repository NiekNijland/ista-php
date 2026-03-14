<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class Customer
{
    public function __construct(
        public string $cuid,
        public ConsumptionPeriod $consumption,
        public string $address = '',
        public string $zip = '',
        public string $city = '',
        public string $dateStart = '',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $consumptionData */
        $consumptionData = $data['curConsumption'] ?? [];

        return new self(
            cuid: (string) ($data['Cuid'] ?? ''),
            consumption: ConsumptionPeriod::fromArray($consumptionData),
            address: (string) ($data['Adress'] ?? ''),
            zip: (string) ($data['Zip'] ?? ''),
            city: (string) ($data['City'] ?? ''),
            dateStart: (string) ($data['DateStart'] ?? ''),
        );
    }

    /**
     * @return array{Cuid: string, Adress: string, Zip: string, City: string, DateStart: string, curConsumption: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'Cuid' => $this->cuid,
            'Adress' => $this->address,
            'Zip' => $this->zip,
            'City' => $this->city,
            'DateStart' => $this->dateStart,
            'curConsumption' => $this->consumption->toArray(),
        ];
    }
}
