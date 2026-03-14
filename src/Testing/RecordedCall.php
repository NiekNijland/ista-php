<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Testing;

readonly class RecordedCall
{
    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __construct(
        public string $method,
        public array $arguments,
    ) {}
}
