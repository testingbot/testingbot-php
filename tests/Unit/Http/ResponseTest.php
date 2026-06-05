<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use TestingBot\Http\Response;

final class ResponseTest extends TestCase
{
    public function testDecodesJsonObject(): void
    {
        $response = new Response(200, '{"a":1,"b":"x"}');

        self::assertSame(['a' => 1, 'b' => 'x'], $response->json());
    }

    public function testDecodesJsonArray(): void
    {
        $response = new Response(200, '["1.2.3.4","5.6.7.8"]');

        self::assertSame(['1.2.3.4', '5.6.7.8'], $response->json());
    }

    public function testEmptyBodyDecodesToNull(): void
    {
        self::assertNull((new Response(204, ''))->json());
    }

    public function testIsSuccessful(): void
    {
        self::assertTrue((new Response(200, ''))->isSuccessful());
        self::assertTrue((new Response(299, ''))->isSuccessful());
        self::assertFalse((new Response(199, ''))->isSuccessful());
        self::assertFalse((new Response(400, ''))->isSuccessful());
        self::assertFalse((new Response(500, ''))->isSuccessful());
    }

    public function testHeaderLookupIsCaseInsensitive(): void
    {
        $response = new Response(429, '', ['Retry-After' => '30']);

        self::assertSame('30', $response->getHeader('retry-after'));
        self::assertSame('30', $response->getHeader('Retry-After'));
        self::assertNull($response->getHeader('x-missing'));
    }
}
