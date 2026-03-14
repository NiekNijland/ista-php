<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class MonthlyDeviceConsumption
{
    public function __construct(
        public int $id,
        public int $output,
        public int $artNr,
        public int $serialNr,
        public string $startDate,
        public float $startValue,
        public string $endDate,
        public float $endValue,
        public float $rawValue,
        public float $calculatedValue,
        public int $value,
        public string $progJump,
        public string $active,
        public string $roomDescription,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['Id'] ?? 0),
            output: (int) ($data['Output'] ?? 0),
            artNr: (int) ($data['ArtNr'] ?? 0),
            serialNr: (int) ($data['SerialNr'] ?? 0),
            startDate: (string) ($data['SDate'] ?? ''),
            startValue: (float) ($data['SValue'] ?? 0.0),
            endDate: (string) ($data['EDate'] ?? ''),
            endValue: (float) ($data['EValue'] ?? 0.0),
            rawValue: (float) ($data['CValue'] ?? 0.0),
            calculatedValue: (float) ($data['CCValue'] ?? 0.0),
            value: (int) ($data['CCDValue'] ?? 0),
            progJump: (string) ($data['ProgJump'] ?? ''),
            active: (string) ($data['Active'] ?? ''),
            roomDescription: (string) ($data['RoomDescr'] ?? ''),
        );
    }

    /**
     * @return array{Id: int, Output: int, ArtNr: int, SerialNr: int, SDate: string, SValue: float, EDate: string, EValue: float, CValue: float, CCValue: float, CCDValue: int, ProgJump: string, Active: string, RoomDescr: string}
     */
    public function toArray(): array
    {
        return [
            'Id' => $this->id,
            'Output' => $this->output,
            'ArtNr' => $this->artNr,
            'SerialNr' => $this->serialNr,
            'SDate' => $this->startDate,
            'SValue' => $this->startValue,
            'EDate' => $this->endDate,
            'EValue' => $this->endValue,
            'CValue' => $this->rawValue,
            'CCValue' => $this->calculatedValue,
            'CCDValue' => $this->value,
            'ProgJump' => $this->progJump,
            'Active' => $this->active,
            'RoomDescr' => $this->roomDescription,
        ];
    }
}
