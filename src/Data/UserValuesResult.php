<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Data;

readonly class UserValuesResult
{
    /**
     * @param  Customer[]  $customers
     */
    public function __construct(
        public array $customers,
        public string $displayName = '',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $customers */
        $customers = $data['Cus'] ?? [];

        return new self(
            customers: array_map(
                static fn (array $customer): Customer => Customer::fromArray($customer),
                $customers,
            ),
            displayName: (string) ($data['DisplayName'] ?? ''),
        );
    }

    /**
     * @return array{DisplayName: string, Cus: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'DisplayName' => $this->displayName,
            'Cus' => array_values(array_map(
                static fn (Customer $customer): array => $customer->toArray(),
                $this->customers,
            )),
        ];
    }
}
