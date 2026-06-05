<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Facade;

use PHPUnit\Framework\TestCase;
use TestingBot\Exception\NotFoundException;
use TestingBot\Http\Multipart;
use TestingBot\Http\Request;
use TestingBot\TestingBotAPI;
use TestingBot\Tests\Support\FakeHttpClient;

/**
 * Verifies the 1.x facade still maps each legacy method onto the right request.
 */
final class BackwardCompatTest extends TestCase
{
    private FakeHttpClient $http;
    private TestingBotAPI $api;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->api = new TestingBotAPI('key', 'secret', [], $this->http);
    }

    private function lastRequest(): Request
    {
        return $this->http->lastRequest();
    }

    public function testUpdateJobWrapsTestFields(): void
    {
        $this->api->updateJob('abc', ['name' => 'x', 'success' => true]);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->method);
        self::assertSame('tests/abc', $request->path);
        self::assertSame(['test[name]' => 'x', 'test[success]' => true], $request->body);
    }

    public function testGetJobsPagination(): void
    {
        $this->api->getJobs(10, 5);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->method);
        self::assertSame('tests', $request->path);
        self::assertSame(['offset' => 10, 'count' => 5], $request->query);
    }

    public function testGetTunnelsUsesListEndpoint(): void
    {
        $this->api->getTunnels();

        self::assertSame('tunnel/list', $this->lastRequest()->path);
    }

    public function testCreateLabTestWrapsNameAndUrl(): void
    {
        $this->api->createLabTest('smoke', 'https://x.com', ['cron' => '@daily']);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->method);
        self::assertSame('lab', $request->path);
        self::assertSame(
            ['test[name]' => 'smoke', 'test[url]' => 'https://x.com', 'test[cron]' => '@daily'],
            $request->body,
        );
    }

    public function testModifyLabTestStepsSendsLegacyRawBody(): void
    {
        $this->api->modifyLabTestSteps(7, [
            ['cmd' => 'open', 'locator' => '/', 'value' => ''],
            ['cmd' => 'click', 'locator' => '#go', 'value' => ''],
        ]);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->method);
        self::assertSame('lab/7/steps', $request->path);
        self::assertSame(
            'steps[][order]=0&steps[][cmd]=open&steps[][locator]=/&steps[][value]='
            . '&steps[][order]=1&steps[][cmd]=click&steps[][locator]=#go&steps[][value]=',
            $request->body,
        );
    }

    public function testUploadLocalFileGoesThroughMultipart(): void
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'tb');
        file_put_contents($path, 'apk');

        try {
            $this->api->uploadLocalFileToStorage($path);
        } finally {
            @unlink($path);
        }

        $request = $this->lastRequest();
        self::assertSame('POST', $request->method);
        self::assertSame('storage', $request->path);
        self::assertInstanceOf(Multipart::class, $request->body);
    }

    public function testGetStorageFileStripsScheme(): void
    {
        $this->api->getStorageFile('tb://abc123');

        self::assertSame('storage/abc123', $this->lastRequest()->path);
    }

    public function testGetAuthenticationHash(): void
    {
        self::assertSame(md5('key:secret'), $this->api->getAuthenticationHash());
        self::assertSame(md5('key:secret:job1'), $this->api->getAuthenticationHash('job1'));
    }

    public function testFailedCallThrowsInsteadOfReturningErrorArray(): void
    {
        $this->http->queueJson(404, ['error' => 'Nothing found']);

        $this->expectException(NotFoundException::class);

        $this->api->getJob('missing');
    }
}
