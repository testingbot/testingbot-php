<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Enum\BrowserType;
use TestingBot\Tests\Unit\ResourceTestCase;

/**
 * Browsers, Devices and Configuration — the catalog/no-or-simple-arg endpoints.
 */
final class CatalogTest extends ResourceTestCase
{
    public function testBrowsersList(): void
    {
        $this->client->browsers()->list();

        $request = $this->assertRequest('GET', 'browsers');
        self::assertSame([], $request->query);
    }

    public function testBrowsersListWithType(): void
    {
        $this->client->browsers()->list(BrowserType::Rc);

        $request = $this->assertRequest('GET', 'browsers');
        self::assertSame(['type' => 'rc'], $request->query);
    }

    public function testDevicesList(): void
    {
        $this->client->devices()->list('android');

        $request = $this->assertRequest('GET', 'devices');
        self::assertSame(['platform' => 'android'], $request->query);
    }

    public function testDevicesAvailable(): void
    {
        $this->client->devices()->available();

        $this->assertRequest('GET', 'devices/available');
    }

    public function testDeviceGet(): void
    {
        $this->client->devices()->get(42);

        $this->assertRequest('GET', 'devices/42');
    }

    public function testIpRangesIsUnauthenticated(): void
    {
        $this->http->queueJson(200, ['1.2.3.4']);

        $ranges = $this->client->configuration()->ipRanges();

        $request = $this->assertRequest('GET', 'configuration/ip-ranges');
        self::assertFalse($request->requiresAuth);
        self::assertSame(['1.2.3.4'], $ranges);
    }
}
