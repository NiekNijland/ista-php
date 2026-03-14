<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class Meter
{
    public function __construct(
        public string $meterId,
        public string $position,
        public int $value,
        public int $meterNr = 0,
        public int $artNr = 0,
        public float $calcFactor = 0.0,
        public int $reduction = 0,
        public int $beginValue = 0,
        public int $endValue = 0,
        public int $rawValue = 0,
        public int $calculatedValue = 0,
        public string $beginDate = '',
        public string $endDate = '',
        public int $serviceId = 0,
        public int $billingPeriodId = 0,
        public int $radiatorNumber = 0,
        public int $order = 0,
        public int $transferLoss = 0,
        public int $multiply = 0,
        public int $decimalPositions = 0,
        public int $estimatedStartValue = 0,
        public int $estimatedEndValue = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            meterId: (string) ($data['MeterId'] ?? ''),
            position: (string) ($data['Position'] ?? ''),
            value: (int) ($data['CCDValue'] ?? 0),
            meterNr: (int) ($data['MeterNr'] ?? 0),
            artNr: (int) ($data['ArtNr'] ?? 0),
            calcFactor: (float) ($data['CalcFactor'] ?? 0.0),
            reduction: (int) ($data['Reduction'] ?? 0),
            beginValue: (int) ($data['BeginValue'] ?? 0),
            endValue: (int) ($data['EndValue'] ?? 0),
            rawValue: (int) ($data['CValue'] ?? 0),
            calculatedValue: (int) ($data['CCValue'] ?? 0),
            beginDate: (string) ($data['BsDate'] ?? ''),
            endDate: (string) ($data['EsDate'] ?? ''),
            serviceId: (int) ($data['serviceId'] ?? 0),
            billingPeriodId: (int) ($data['BillingPeriodId'] ?? 0),
            radiatorNumber: (int) ($data['RadNr'] ?? 0),
            order: (int) ($data['Order'] ?? 0),
            transferLoss: (int) ($data['TransferLoss'] ?? 0),
            multiply: (int) ($data['Multiply'] ?? 0),
            decimalPositions: (int) ($data['DecPos'] ?? 0),
            estimatedStartValue: (int) ($data['SValR'] ?? 0),
            estimatedEndValue: (int) ($data['EvalR'] ?? 0),
        );
    }

    /**
     * @return array{MeterId: string, serviceId: int, BillingPeriodId: int, RadNr: int, Order: int, Position: string, ArtNr: int, MeterNr: int, TransferLoss: int, Multiply: int, Reduction: int, CalcFactor: float, BsDate: string, BeginValue: int, EsDate: string, EndValue: int, CValue: int, CCValue: int, CCDValue: int, DecPos: int, SValR: int, EvalR: int}
     */
    public function toArray(): array
    {
        return [
            'MeterId' => $this->meterId,
            'serviceId' => $this->serviceId,
            'BillingPeriodId' => $this->billingPeriodId,
            'RadNr' => $this->radiatorNumber,
            'Order' => $this->order,
            'Position' => $this->position,
            'ArtNr' => $this->artNr,
            'MeterNr' => $this->meterNr,
            'TransferLoss' => $this->transferLoss,
            'Multiply' => $this->multiply,
            'Reduction' => $this->reduction,
            'CalcFactor' => $this->calcFactor,
            'BsDate' => $this->beginDate,
            'BeginValue' => $this->beginValue,
            'EsDate' => $this->endDate,
            'EndValue' => $this->endValue,
            'CValue' => $this->rawValue,
            'CCValue' => $this->calculatedValue,
            'CCDValue' => $this->value,
            'DecPos' => $this->decimalPositions,
            'SValR' => $this->estimatedStartValue,
            'EvalR' => $this->estimatedEndValue,
        ];
    }
}
