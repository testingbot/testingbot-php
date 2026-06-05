<?php

declare(strict_types=1);

namespace TestingBot\Http;

use TestingBot\Exception\InvalidArgumentException;

/**
 * A `multipart/form-data` request body. Wraps a set of fields where any value
 * may be a {@see \CURLFile} (a file part) or a scalar string (a plain field).
 *
 * Passing the {@see Multipart::getFields()} array straight to
 * `CURLOPT_POSTFIELDS` makes cURL switch to multipart encoding automatically.
 */
final class Multipart
{
    /**
     * @param array<string, \CURLFile|string> $fields
     */
    public function __construct(private readonly array $fields)
    {
    }

    /**
     * Build a multipart body from a local file, detecting its MIME type with
     * the fileinfo extension (replacing the removed `mime_content_type()`).
     *
     * @param array<string, string> $extra Additional plain form fields.
     */
    public static function fromFile(string $localFile, string $field = 'file', array $extra = []): self
    {
        if (!is_file($localFile)) {
            throw new InvalidArgumentException('Could not find file to upload: ' . $localFile);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($localFile) ?: 'application/octet-stream';
        $name = basename($localFile);

        return new self([$field => new \CURLFile($localFile, $mime, $name)] + $extra);
    }

    /**
     * @return array<string, \CURLFile|string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
