<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Tests\Unit\ResourceTestCase;

final class TunnelsAndUserTest extends ResourceTestCase
{
    public function testTunnelList(): void
    {
        $this->client->tunnels()->list();

        $this->assertRequest('GET', 'tunnel/list');
    }

    public function testTunnelGetActive(): void
    {
        $this->client->tunnels()->getActive();

        $this->assertRequest('GET', 'tunnel');
    }

    public function testTunnelGet(): void
    {
        $this->client->tunnels()->get(7);

        $this->assertRequest('GET', 'tunnel/7');
    }

    public function testTunnelCreate(): void
    {
        $this->client->tunnels()->create(['region' => 'eu']);

        $request = $this->assertRequest('POST', 'tunnel/create');
        self::assertSame(['region' => 'eu'], $request->body);
    }

    public function testTunnelDelete(): void
    {
        $this->client->tunnels()->delete(7);

        $this->assertRequest('DELETE', 'tunnel/7');
    }

    public function testTunnelDeleteActive(): void
    {
        $this->client->tunnels()->deleteActive();

        $this->assertRequest('DELETE', 'tunnel');
    }

    public function testUserGet(): void
    {
        $this->client->user()->get();

        $this->assertRequest('GET', 'user');
    }

    public function testUserKeys(): void
    {
        $this->client->user()->keys();

        $this->assertRequest('GET', 'user/keys');
    }

    public function testUserUpdateWraps(): void
    {
        $this->client->user()->update(['first_name' => 'Jane']);

        $request = $this->assertRequest('PUT', 'user');
        self::assertSame(['user[first_name]' => 'Jane'], $request->body);
    }
}
