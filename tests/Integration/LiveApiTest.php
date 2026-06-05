<?php

declare(strict_types=1);

namespace TestingBot\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TestingBot\Client;
use TestingBot\Enum\BrowserType;
use TestingBot\Exception\NotFoundException;
use TestingBot\TestingBotAPI;

/**
 * Integration tests that hit the live TestingBot API. Skipped automatically
 * unless TB_KEY and TB_SECRET are present in the environment.
 *
 * These assert on the *shape* of responses (keys present, types) rather than
 * account-specific magic values, so they are stable across accounts.
 *
 * @group integration
 */
final class LiveApiTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $key = getenv('TB_KEY');
        $secret = getenv('TB_SECRET');

        if ($key === false || $secret === false || $key === '' || $secret === '') {
            self::markTestSkipped('Set TB_KEY and TB_SECRET to run live integration tests.');
        }

        $this->client = new Client($key, $secret);
    }

    public function testIpRangesIsAFlatListOfStrings(): void
    {
        $ranges = $this->client->configuration()->ipRanges();

        self::assertNotEmpty($ranges);
        self::assertContainsOnly('string', $ranges);
    }

    public function testGetBrowsersReturnsEnvironments(): void
    {
        $browsers = $this->client->browsers()->list(BrowserType::Webdriver);

        self::assertNotEmpty($browsers);
        self::assertArrayHasKey('name', $browsers[0]);
    }

    public function testGetUserInfoHasProfileFields(): void
    {
        $user = $this->client->user()->get();

        self::assertArrayHasKey('first_name', $user);
        self::assertArrayHasKey('last_name', $user);
    }

    public function testListTestsIsPaginated(): void
    {
        $tests = $this->client->tests()->list(0, 10);

        self::assertArrayHasKey('data', $tests);
        self::assertArrayHasKey('meta', $tests);
        self::assertIsInt($tests['meta']['total']);
    }

    public function testUnknownTestThrowsNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->client->tests()->get('this-session-does-not-exist');
    }

    public function testStorageUploadRoundTrip(): void
    {
        $apk = __DIR__ . '/../resources/test.apk';
        $uploaded = $this->client->storage()->uploadFile($apk);

        self::assertArrayHasKey('app_url', $uploaded);
        self::assertStringContainsString('tb://', $uploaded['app_url']);

        $meta = $this->client->storage()->get($uploaded['app_url']);
        self::assertSame($uploaded['app_url'], $meta['app_url']);

        $this->client->storage()->delete($uploaded['app_url']);
    }

    public function testLegacyFacadeStillWorks(): void
    {
        $api = new TestingBotAPI((string) getenv('TB_KEY'), (string) getenv('TB_SECRET'));
        $user = $api->getUserInfo();

        self::assertArrayHasKey('first_name', $user);
    }
}
