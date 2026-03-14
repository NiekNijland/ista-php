<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class BillingService
{
    public function __construct(
        public int $id,
        public string $description,
        public string $meterType,
        public string $unit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['Id'] ?? 0),
            description: (string) ($data['Description'] ?? ''),
            meterType: (string) ($data['MeterType'] ?? ''),
            unit: (string) ($data['Unit'] ?? ''),
        );
    }

    /**
     * @return array{Id: int, Description: string, MeterType: string, Unit: string}
     */
    public function toArray(): array
    {
        return [
            'Id' => $this->id,
            'Description' => $this->description,
            'MeterType' => $this->meterType,
            'Unit' => $this->unit,
        ];
    }
}
