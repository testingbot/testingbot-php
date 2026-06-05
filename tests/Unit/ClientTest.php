<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TestingBot\Client;
use TestingBot\Configuration\ClientConfig;
use TestingBot\Exception\ApiException;
use TestingBot\Exception\InvalidArgumentException;
use TestingBot\Http\Request;
use TestingBot\Tests\Support\FakeHttpClient;

final class ClientTest extends TestCase
{
    public function testRequiresKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Client('', 'secret');
    }

    public function testRequiresSecret(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Client('key', '');
    }

    public function testBaseUrlGetsTrailingSlash(): void
    {
        $config = ClientConfig::fromArray('key', 'secret', ['base_url' => 'https://example.test/v1']);

        self::assertSame('https://example.test/v1/', $config->baseUrl);
    }

    public function testResourceAccessorsAreMemoised(): void
    {
        $client = new Client('key', 'secret', [], new FakeHttpClient());

        self::assertSame($client->tests(), $client->tests());
    }

    public function testRequestEscapeHatchReturnsDecodedBody(): void
    {
        $http = new FakeHttpClient();
        $http->queueJson(200, ['ok' => true]);
        $client = new Client('key', 'secret', [], $http);

        $result = $client->request(new Request('GET', 'anything'));

        self::assertSame(['ok' => true], $result);
    }

    public function testRequestEscapeHatchThrowsOnError(): void
    {
        $http = new FakeHttpClient();
        $http->queueJson(500, ['message' => 'boom']);
        $client = new Client('key', 'secret', [], $http);

        $this->expectException(ApiException::class);

        $client->request(new Request('GET', 'anything'));
    }

    public function testUserAgentDefaultsToVersionedString(): void
    {
        $config = ClientConfig::fromArray('key', 'secret');

        self::assertStringContainsString('testingbot-php/' . Client::VERSION, $config->userAgent);
    }
}
