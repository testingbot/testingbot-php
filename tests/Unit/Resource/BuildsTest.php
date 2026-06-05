<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Tests\Unit\ResourceTestCase;

final class BuildsTest extends ResourceTestCase
{
    public function testList(): void
    {
        $this->client->builds()->list(1, 5);

        $request = $this->assertRequest('GET', 'builds');
        self::assertSame(['offset' => 1, 'count' => 5], $request->query);
    }

    public function testGetTests(): void
    {
        $this->client->builds()->getTests('ci-42', 0, 100);

        $request = $this->assertRequest('GET', 'builds/ci-42');
        self::assertSame(['offset' => 0, 'count' => 100], $request->query);
    }

    public function testDelete(): void
    {
        $this->client->builds()->delete('99');

        $this->assertRequest('DELETE', 'builds/99');
    }
}
