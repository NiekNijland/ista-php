<?php

declare(strict_types=1);

namespace NiekNijland\Ista\Tests\Unit\Data;

use NiekNijland\Ista\Support\JwtToken;
use PHPUnit\Framework\TestCase;

class JwtTokenTest extends TestCase
{
    public function test_to_string_returns_token(): void
    {
        $token = new JwtToken('some-jwt-token');

        $this->assertSame('some-jwt-token', $token->toString());
    }

    public function test_valid_unexpired_token_is_not_expired(): void
    {
        // Create a JWT with exp far in the future
        $payload = base64_encode(json_encode(['exp' => time() + 3600]));
        $token = new JwtToken("eyJhbGciOiJIUzI1NiJ9.{$payload}.signature");

        $this->assertFalse($token->isExpired());
    }

    public function test_expired_token_is_expired(): void
    {
        // Create a JWT with exp in the past
        $payload = base64_encode(json_encode(['exp' => time() - 3600]));
        $token = new JwtToken("eyJhbGciOiJIUzI1NiJ9.{$payload}.signature");

        $this->assertTrue($token->isExpired());
    }

    public function test_invalid_jwt_format_is_expired(): void
    {
        $token = new JwtToken('not-a-jwt');

        $this->assertTrue($token->isExpired());
    }

    public function test_jwt_without_exp_claim_is_expired(): void
    {
        $payload = base64_encode(json_encode(['sub' => '1234']));
        $token = new JwtToken("eyJhbGciOiJIUzI1NiJ9.{$payload}.signature");

        $this->assertTrue($token->isExpired());
    }

    public function test_jwt_with_invalid_payload_is_expired(): void
    {
        $token = new JwtToken('header.!!!invalid-base64!!!.signature');

        $this->assertTrue($token->isExpired());
    }
}
