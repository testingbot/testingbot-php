<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * Cross-browser screenshot batches.
 */
final class Screenshots extends AbstractResource
{
    /**
     * List screenshot batches.
     *
     * @return array<mixed>
     */
    public function list(int $offset = 0, int $count = 10): array
    {
        return $this->request(new Request('GET', 'screenshots', query: $this->paginationQuery($offset, $count)));
    }

    /**
     * Get a screenshot batch with its per-browser results.
     *
     * @param array<string, mixed> $options Query options: `excludeIds`, `omit`.
     * @return array<mixed>
     */
    public function get(int $id, array $options = []): array
    {
        return $this->request(new Request('GET', 'screenshots/' . $id, query: $options));
    }

    /**
     * Capture cross-browser screenshots of a URL.
     *
     * @param list<int|string|array<string, mixed>> $browsers Browser ids or specs.
     * @param array<string, mixed> $options Must include `resolution`; may include
     *        `wait_time`, `fullpage`, `callback_url`.
     * @return array<mixed>
     */
    public function create(string $url, array $browsers, array $options = []): array
    {
        $body = ['url' => $url, 'browsers' => $browsers] + $options;

        return $this->request(new Request('POST', 'screenshots', body: $body));
    }
}
