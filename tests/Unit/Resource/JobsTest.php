<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Exception\TestingBotException;
use TestingBot\Tests\Unit\ResourceTestCase;

final class JobsTest extends ResourceTestCase
{
    public function testGet(): void
    {
        $this->client->jobs()->get(123);

        $this->assertRequest('GET', 'jobs/123');
    }

    public function testWaitForCompletionReturnsOnTerminalStatus(): void
    {
        $this->http->queueJson(200, ['status' => 'FINISHED', 'success' => true]);

        $job = $this->client->jobs()->waitForCompletion(123, timeout: 5, interval: 0);

        self::assertSame('FINISHED', $job['status']);
    }

    public function testWaitForCompletionTimesOut(): void
    {
        $this->http->queueJson(200, ['status' => 'RUNNING']);

        $this->expectException(TestingBotException::class);

        $this->client->jobs()->waitForCompletion(123, timeout: 0, interval: 0);
    }
}
