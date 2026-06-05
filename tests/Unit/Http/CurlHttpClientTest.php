<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use TestingBot\Configuration\ClientConfig;
use TestingBot\Exception\NetworkException;
use TestingBot\Http\CurlHttpClient;
use TestingBot\Http\Request;

final class CurlHttpClientTest extends TestCase
{
    public function testTransportFailureThrowsNetworkException(): void
    {
        // Port 1 on loopback refuses immediately: no DNS, no network, fast + reliable.
        $config = ClientConfig::fromArray('key', 'secret', [
            'base_url' => 'http://127.0.0.1:1/',
            'connect_timeout' => 1,
            'timeout' => 1,
        ]);
        $client = new CurlHttpClient($config);

        $this->expectException(NetworkException::class);

        $client->send(new Request('GET', 'user'));
    }
}
