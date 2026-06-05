<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Http\Multipart;
use TestingBot\Tests\Unit\ResourceTestCase;

final class LabTest extends ResourceTestCase
{
    public function testListTests(): void
    {
        $this->client->lab()->listTests(0, 10);

        $request = $this->assertRequest('GET', 'lab');
        self::assertSame(['offset' => 0, 'count' => 10], $request->query);
    }

    public function testCreateTestWrapsFields(): void
    {
        $this->client->lab()->createTest(['name' => 'smoke', 'url' => 'https://x.com']);

        $request = $this->assertRequest('POST', 'lab');
        self::assertSame(['test[name]' => 'smoke', 'test[url]' => 'https://x.com'], $request->body);
    }

    public function testCreateTestWithFileUsesMultipart(): void
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'tb');
        file_put_contents($path, 'side-export');

        try {
            $this->client->lab()->createTest(['name' => 'imported', 'screenshot' => true], $path);
        } finally {
            @unlink($path);
        }

        $request = $this->assertRequest('POST', 'lab');
        self::assertInstanceOf(Multipart::class, $request->body);
        $fields = $request->body->getFields();
        self::assertArrayHasKey('file', $fields);
        self::assertSame('imported', $fields['test[name]']);
        self::assertSame('1', $fields['test[screenshot]']);
    }

    public function testTriggerWithUrl(): void
    {
        $this->client->lab()->trigger(5, 'https://staging.example.com');

        $request = $this->assertRequest('POST', 'lab/5/trigger');
        self::assertSame(['url' => 'https://staging.example.com'], $request->body);
    }

    public function testTriggerAllWithoutUrl(): void
    {
        $this->client->lab()->triggerAll();

        $request = $this->assertRequest('POST', 'lab/trigger_all');
        self::assertSame([], $request->body);
    }

    public function testStopWithBrowser(): void
    {
        $this->client->lab()->stop(5, 12);

        $request = $this->assertRequest('PUT', 'lab/5/stop');
        self::assertSame(['browser_id' => 12], $request->body);
    }

    public function testSetStepsSendsNestedArray(): void
    {
        $steps = [['order' => 0, 'cmd' => 'open', 'locator' => '/', 'value' => '']];
        $this->client->lab()->setSteps(5, $steps);

        $request = $this->assertRequest('POST', 'lab/5/steps');
        self::assertSame(['steps' => $steps], $request->body);
    }

    public function testSetBrowsersJoinsIds(): void
    {
        $this->client->lab()->setBrowsers(5, [1, 2, 3]);

        $request = $this->assertRequest('POST', 'lab/5/browsers');
        self::assertSame(['browser_ids' => '1,2,3'], $request->body);
    }

    public function testScheduleAlertReport(): void
    {
        $this->client->lab()->schedule(5, ['type' => 'daily', 'hour' => '08:00']);
        $this->assertRequest('POST', 'lab/5/schedule');

        $this->client->lab()->addAlert(5, ['kind' => 'EMAIL', 'level' => 'IMMEDIATELY', 'content' => 'a@b.com']);
        $this->assertRequest('POST', 'lab/5/alert');

        $this->client->lab()->updateReport(5, ['email' => 'a@b.com']);
        $this->assertRequest('PUT', 'lab/5/report');
    }
}
