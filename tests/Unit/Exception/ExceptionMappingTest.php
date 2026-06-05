<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use TestingBot\Exception\ApiException;
use TestingBot\Exception\AuthenticationException;
use TestingBot\Exception\NotFoundException;
use TestingBot\Exception\RateLimitException;
use TestingBot\Http\Response;

final class ExceptionMappingTest extends TestCase
{
    public function testMapsUnauthorized(): void
    {
        $e = ApiException::fromResponse(new Response(401, '{"error":"401 Unauthorized"}'));

        self::assertInstanceOf(AuthenticationException::class, $e);
        self::assertSame(401, $e->getStatusCode());
        self::assertSame('401 Unauthorized', $e->getMessage());
    }

    public function testMapsForbiddenToAuthentication(): void
    {
        $e = ApiException::fromResponse(new Response(403, '{"error":"read-only"}'));

        self::assertInstanceOf(AuthenticationException::class, $e);
    }

    public function testMapsNotFound(): void
    {
        $e = ApiException::fromResponse(new Response(404, '{"error":"Nothing found"}'));

        self::assertInstanceOf(NotFoundException::class, $e);
        self::assertSame(['error' => 'Nothing found'], $e->getDecoded());
    }

    public function testMapsRateLimitWithRetryAfter(): void
    {
        $e = ApiException::fromResponse(new Response(429, '{"message":"slow down"}', ['Retry-After' => '42']));

        self::assertInstanceOf(RateLimitException::class, $e);
        self::assertSame(42, $e->getRetryAfter());
        self::assertSame('slow down', $e->getMessage());
    }

    public function testGenericServerError(): void
    {
        $e = ApiException::fromResponse(new Response(500, 'not json'));

        self::assertSame(ApiException::class, $e::class);
        self::assertSame(500, $e->getStatusCode());
        self::assertSame('not json', $e->getResponseBody());
        self::assertNull($e->getDecoded());
        self::assertStringContainsString('HTTP 500', $e->getMessage());
    }
}
