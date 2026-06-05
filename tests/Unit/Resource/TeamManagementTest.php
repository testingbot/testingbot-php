<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Tests\Unit\ResourceTestCase;

final class TeamManagementTest extends ResourceTestCase
{
    public function testGet(): void
    {
        $this->client->teamManagement()->get();

        $this->assertRequest('GET', 'team-management');
    }

    public function testListUsers(): void
    {
        $this->client->teamManagement()->listUsers(0, 25);

        $request = $this->assertRequest('GET', 'team-management/users');
        self::assertSame(['offset' => 0, 'count' => 25], $request->query);
    }

    public function testGetUser(): void
    {
        $this->client->teamManagement()->getUser(8);

        $this->assertRequest('GET', 'team-management/users/8');
    }

    public function testGetUserClientKey(): void
    {
        $this->client->teamManagement()->getUserClientKey(8);

        $this->assertRequest('GET', 'team-management/users/8/client-key');
    }

    public function testResetUserKeys(): void
    {
        $this->client->teamManagement()->resetUserKeys(8);

        $this->assertRequest('POST', 'team-management/users/8/reset-keys');
    }

    public function testCreateUserSendsFlatBody(): void
    {
        $this->client->teamManagement()->createUser(['email' => 'a@b.com', 'password' => 'x']);

        $request = $this->assertRequest('POST', 'team-management/users');
        self::assertSame(['email' => 'a@b.com', 'password' => 'x'], $request->body);
    }

    public function testUpdateUser(): void
    {
        $this->client->teamManagement()->updateUser(8, ['credits' => 100]);

        $request = $this->assertRequest('PUT', 'team-management/users/8');
        self::assertSame(['credits' => 100], $request->body);
    }
}
