<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Exception\InvalidArgumentException;
use TestingBot\Tests\Unit\ResourceTestCase;

final class TestsTest extends ResourceTestCase
{
    public function testList(): void
    {
        $this->client->tests()->list(5, 20, ['build' => 'ci-7', 'browser_id' => 3]);

        $request = $this->assertRequest('GET', 'tests');
        self::assertSame(['offset' => 5, 'count' => 20, 'build' => 'ci-7', 'browser_id' => 3], $request->query);
    }

    public function testGet(): void
    {
        $this->client->tests()->get('abc123', ['skip_fields' => 'logs']);

        $request = $this->assertRequest('GET', 'tests/abc123');
        self::assertSame(['skip_fields' => 'logs'], $request->query);
    }

    public function testGetRejectsEmptyId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client->tests()->get('');
    }

    public function testUpdateWrapsFields(): void
    {
        $this->client->tests()->update('abc123', ['name' => 'login', 'success' => true]);

        $request = $this->assertRequest('PUT', 'tests/abc123');
        self::assertSame(['test[name]' => 'login', 'test[success]' => true], $request->body);
    }

    public function testCreate(): void
    {
        $this->client->tests()->create(['name' => 'manual']);

        $request = $this->assertRequest('POST', 'tests');
        self::assertSame(['test[name]' => 'manual'], $request->body);
    }

    public function testDelete(): void
    {
        $this->client->tests()->delete('abc123');

        $this->assertRequest('DELETE', 'tests/abc123');
    }

    public function testStop(): void
    {
        $this->client->tests()->stop('abc123');

        $this->assertRequest('PUT', 'tests/abc123/stop');
    }
}
