<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TestingBot\Client;
use TestingBot\Http\Request;
use TestingBot\Tests\Support\FakeHttpClient;

/**
 * Base class for resource unit tests: wires a {@see Client} to a
 * {@see FakeHttpClient} and helps assert the request that was shaped.
 */
abstract class ResourceTestCase extends TestCase
{
    protected FakeHttpClient $http;
    protected Client $client;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->client = new Client('key', 'secret', [], $this->http);
    }

    protected function assertRequest(string $method, string $path): Request
    {
        $request = $this->http->lastRequest();
        self::assertSame($method, $request->method, 'HTTP method');
        self::assertSame($path, $request->path, 'request path');

        return $request;
    }
}
