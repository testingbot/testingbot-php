<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Resource;

use TestingBot\Http\Multipart;
use TestingBot\Tests\Unit\ResourceTestCase;

final class StorageTest extends ResourceTestCase
{
    public function testUploadFileSendsMultipart(): void
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'tb');
        file_put_contents($path, 'binary');

        try {
            $this->client->storage()->uploadFile($path);
        } finally {
            @unlink($path);
        }

        $request = $this->assertRequest('POST', 'storage');
        self::assertInstanceOf(Multipart::class, $request->body);
        self::assertArrayHasKey('file', $request->body->getFields());
    }

    public function testUploadRemoteUrl(): void
    {
        $this->client->storage()->uploadRemoteUrl('https://example.com/app.apk');

        $request = $this->assertRequest('POST', 'storage');
        self::assertSame(['url' => 'https://example.com/app.apk'], $request->body);
    }

    public function testReplaceStripsScheme(): void
    {
        $this->client->storage()->replaceRemoteUrl('tb://abc', 'https://example.com/new.apk');

        $request = $this->assertRequest('POST', 'storage/abc');
        self::assertSame(['url' => 'https://example.com/new.apk'], $request->body);
    }

    public function testList(): void
    {
        $this->client->storage()->list(2, 50);

        $request = $this->assertRequest('GET', 'storage');
        self::assertSame(['offset' => 2, 'count' => 50], $request->query);
    }

    public function testGetStripsScheme(): void
    {
        $this->client->storage()->get('tb://abc123');

        $this->assertRequest('GET', 'storage/abc123');
    }

    public function testDeleteStripsScheme(): void
    {
        $this->client->storage()->delete('tb://abc123');

        $this->assertRequest('DELETE', 'storage/abc123');
    }
}
