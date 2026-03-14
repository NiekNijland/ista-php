<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class ConsumptionValuesResult
{
    /**
     * @param  ServiceComparison[]  $services
     */
    public function __construct(
        public string $curStart,
        public string $curEnd,
        public array $services = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $servicesComp */
        $servicesComp = $data['ServicesComp'] ?? [];

        return new self(
            curStart: (string) ($data['CurStart'] ?? ''),
            curEnd: (string) ($data['CurEnd'] ?? ''),
            services: array_map(
                static fn (array $service): ServiceComparison => ServiceComparison::fromArray($service),
                $servicesComp,
            ),
        );
    }

    /**
     * @return array{CurStart: string, CurEnd: string, ServicesComp: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'CurStart' => $this->curStart,
            'CurEnd' => $this->curEnd,
            'ServicesComp' => array_values(array_map(
                static fn (ServiceComparison $service): array => $service->toArray(),
                $this->services,
            )),
        ];
    }
}
