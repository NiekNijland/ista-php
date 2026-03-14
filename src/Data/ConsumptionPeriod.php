<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

use DateTimeImmutable;
use NiekNijland\Ista\Exception\IstaException;

readonly class ConsumptionPeriod
{
    /**
     * @param  ServiceComparison[]  $services
     * @param  BillingService[]  $billingServices
     * @param  BillingPeriod[]  $billingPeriods
     */
    public function __construct(
        public DateTimeImmutable $start,
        public DateTimeImmutable $end,
        public array $services,
        public string $compStart = '',
        public string $compEnd = '',
        public float $curAverageTemp = 0.0,
        public float $compAverageTemp = 0.0,
        public array $billingServices = [],
        public array $billingPeriods = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $start = self::parseDate((string) ($data['CurStart'] ?? ''));
        $end = self::parseDate((string) ($data['CurEnd'] ?? ''));

        /** @var list<array<string, mixed>> $servicesComp */
        $servicesComp = $data['ServicesComp'] ?? [];

        /** @var list<array<string, mixed>> $billingServicesData */
        $billingServicesData = $data['Billingservices'] ?? [];

        /** @var list<array<string, mixed>> $billingPeriodsData */
        $billingPeriodsData = $data['BillingPeriods'] ?? [];

        return new self(
            start: $start,
            end: $end,
            services: array_map(
                static fn (array $service): ServiceComparison => ServiceComparison::fromArray($service),
                $servicesComp,
            ),
            compStart: (string) ($data['CompStart'] ?? ''),
            compEnd: (string) ($data['CompEnd'] ?? ''),
            curAverageTemp: (float) ($data['CurAverageTemp'] ?? 0.0),
            compAverageTemp: (float) ($data['CompAverageTemp'] ?? 0.0),
            billingServices: array_map(
                static fn (array $service): BillingService => BillingService::fromArray($service),
                $billingServicesData,
            ),
            billingPeriods: array_map(
                static fn (array $period): BillingPeriod => BillingPeriod::fromArray($period),
                $billingPeriodsData,
            ),
        );
    }

    /**
     * @return array{CurStart: string, CurEnd: string, CompStart: string, CompEnd: string, CurAverageTemp: float, CompAverageTemp: float, Billingservices: list<array<string, mixed>>, BillingPeriods: list<array<string, mixed>>, ServicesComp: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'CurStart' => $this->start->format('d-m-Y'),
            'CurEnd' => $this->end->format('d-m-Y'),
            'CompStart' => $this->compStart,
            'CompEnd' => $this->compEnd,
            'CurAverageTemp' => $this->curAverageTemp,
            'CompAverageTemp' => $this->compAverageTemp,
            'Billingservices' => array_values(array_map(
                static fn (BillingService $service): array => $service->toArray(),
                $this->billingServices,
            )),
            'BillingPeriods' => array_values(array_map(
                static fn (BillingPeriod $period): array => $period->toArray(),
                $this->billingPeriods,
            )),
            'ServicesComp' => array_values(array_map(
                static fn (ServiceComparison $service): array => $service->toArray(),
                $this->services,
            )),
        ];
    }

    private static function parseDate(string $dateString): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('d-m-Y', $dateString);

        if ($date === false) {
            throw new IstaException("Failed to parse date '{$dateString}'. Expected format: dd-mm-YYYY.");
        }

        return $date->setTime(0, 0);
    }
}
