<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Support;

use JsonException;

readonly class JwtToken
{
    public function __construct(
        public string $token,
    ) {}

    public function isExpired(): bool
    {
        $parts = explode('.', $this->token);

        if (count($parts) !== 3) {
            return true;
        }

        $payload = base64_decode(strtr($parts[1], '-_', '+/'), true);

        if ($payload === false) {
            return true;
        }

        try {
            /** @var array<string, mixed> $claims */
            $claims = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return true;
        }

        if (! isset($claims['exp']) || ! is_numeric($claims['exp'])) {
            return true;
        }

        return (int) $claims['exp'] < time();
    }

    public function toString(): string
    {
        return $this->token;
    }
}
