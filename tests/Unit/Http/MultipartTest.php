<?php

declare(strict_types=1);

namespace TestingBot\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use TestingBot\Exception\InvalidArgumentException;
use TestingBot\Http\Multipart;

final class MultipartTest extends TestCase
{
    public function testFromFileBuildsCurlFileWithDetectedMime(): void
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'tb');
        file_put_contents($path, '{"hello":"world"}');

        try {
            $multipart = Multipart::fromFile($path, 'file', ['name' => 'demo']);
            $fields = $multipart->getFields();

            self::assertArrayHasKey('file', $fields);
            self::assertInstanceOf(\CURLFile::class, $fields['file']);
            self::assertSame('application/json', $fields['file']->getMimeType());
            self::assertSame('demo', $fields['name']);
        } finally {
            @unlink($path);
        }
    }

    public function testFromFileThrowsWhenMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Multipart::fromFile('/no/such/file.apk');
    }
}
