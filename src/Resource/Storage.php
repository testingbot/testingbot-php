<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Multipart;
use TestingBot\Http\Request;

/**
 * App storage: upload APK/IPA files (or have TestingBot fetch them by URL) and
 * reference them in tests via their `tb://` app URL.
 */
final class Storage extends AbstractResource
{
    /**
     * Upload a local app file.
     *
     * @return array<mixed>
     */
    public function uploadFile(string $localFile): array
    {
        return $this->request(new Request('POST', 'storage', body: Multipart::fromFile($localFile)));
    }

    /**
     * Have TestingBot fetch and store an app from a public URL.
     *
     * @return array<mixed>
     */
    public function uploadRemoteUrl(string $url): array
    {
        return $this->request(new Request('POST', 'storage', body: ['url' => $url]));
    }

    /**
     * Replace an already-stored app (by app key or `tb://` URL) with a local file.
     *
     * @return array<mixed>
     */
    public function replaceFile(string $appUrl, string $localFile): array
    {
        $key = $this->stripAppScheme($appUrl);

        return $this->request(new Request('POST', 'storage/' . $key, body: Multipart::fromFile($localFile)));
    }

    /**
     * Replace an already-stored app (by app key or `tb://` URL) from a public URL.
     *
     * @return array<mixed>
     */
    public function replaceRemoteUrl(string $appUrl, string $url): array
    {
        $key = $this->stripAppScheme($appUrl);

        return $this->request(new Request('POST', 'storage/' . $key, body: ['url' => $url]));
    }

    /**
     * List stored app files.
     *
     * @return array<mixed>
     */
    public function list(int $offset = 0, int $count = 10): array
    {
        return $this->request(new Request('GET', 'storage', query: $this->paginationQuery($offset, $count)));
    }

    /**
     * Get metadata for a stored app (by app key or `tb://` URL).
     *
     * @return array<mixed>
     */
    public function get(string $appUrl): array
    {
        return $this->request(new Request('GET', 'storage/' . $this->stripAppScheme($appUrl)));
    }

    /**
     * Delete a stored app (by app key or `tb://` URL).
     *
     * @return array<mixed>
     */
    public function delete(string $appUrl): array
    {
        return $this->request(new Request('DELETE', 'storage/' . $this->stripAppScheme($appUrl)));
    }
}
