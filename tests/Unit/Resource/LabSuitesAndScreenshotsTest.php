<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Tests\Unit\ResourceTestCase;

final class LabSuitesAndScreenshotsTest extends ResourceTestCase
{
    public function testSuiteCreateWraps(): void
    {
        $this->client->labSuites()->create(['name' => 'Regression']);

        $request = $this->assertRequest('POST', 'labsuites');
        self::assertSame(['suite[name]' => 'Regression'], $request->body);
    }

    public function testSuiteAddTestsJoinsIds(): void
    {
        $this->client->labSuites()->addTests(3, [10, 11]);

        $request = $this->assertRequest('POST', 'labsuites/3/tests');
        self::assertSame(['test_ids' => '10,11'], $request->body);
    }

    public function testSuiteRemoveTest(): void
    {
        $this->client->labSuites()->removeTest(3, 11);

        $this->assertRequest('DELETE', 'labsuites/3/tests/11');
    }

    public function testSuiteTrigger(): void
    {
        $this->client->labSuites()->trigger(3);

        $this->assertRequest('POST', 'labsuites/3/trigger');
    }

    public function testScreenshotsList(): void
    {
        $this->client->screenshots()->list(0, 10);

        $this->assertRequest('GET', 'screenshots');
    }

    public function testScreenshotsGetWithOptions(): void
    {
        $this->client->screenshots()->get(99, ['excludeIds' => '1,2']);

        $request = $this->assertRequest('GET', 'screenshots/99');
        self::assertSame(['excludeIds' => '1,2'], $request->query);
    }

    public function testScreenshotsCreate(): void
    {
        $this->client->screenshots()->create(
            'https://example.com',
            [1, 2],
            ['resolution' => '1920x1080', 'fullpage' => true],
        );

        $request = $this->assertRequest('POST', 'screenshots');
        self::assertSame(
            ['url' => 'https://example.com', 'browsers' => [1, 2], 'resolution' => '1920x1080', 'fullpage' => true],
            $request->body,
        );
    }
}
