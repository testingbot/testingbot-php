<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * Test (job) records. A test is identified by its numeric id or its WebDriver
 * session id.
 */
final class Tests extends AbstractResource
{
    /**
     * List tests, most recent first.
     *
     * @param array<string, mixed> $filters Extra query filters: `since`,
     *        `browser_id`, `group`, `build`, `skip_fields`, `omit`.
     * @return array<mixed>
     */
    public function list(int $offset = 0, int $count = 10, array $filters = []): array
    {
        return $this->request(new Request('GET', 'tests', query: $this->paginationQuery($offset, $count, $filters)));
    }

    /**
     * Get a single test by id or session id.
     *
     * @param array<string, mixed> $options Query options: `skip_fields`, `omit`.
     * @return array<mixed>
     */
    public function get(string $id, array $options = []): array
    {
        $id = $this->requireNonEmpty($id, 'Please use a valid test id or WebDriver session id');

        return $this->request(new Request('GET', 'tests/' . $id, query: $options));
    }

    /**
     * Update a test's metadata (name, success, status_message, extra, build…).
     *
     * @param array<string, mixed> $details
     * @return array<mixed>
     */
    public function update(string $id, array $details): array
    {
        $id = $this->requireNonEmpty($id, 'Please use a valid test id or WebDriver session id');

        return $this->request(new Request('PUT', 'tests/' . $id, body: $this->wrap('test', $details)));
    }

    /**
     * Create a test record (for reporting an externally run result).
     *
     * @param array<string, mixed> $details
     * @return array<mixed>
     */
    public function create(array $details): array
    {
        return $this->request(new Request('POST', 'tests', body: $this->wrap('test', $details)));
    }

    /**
     * Delete a test and its assets.
     *
     * @return array<mixed>
     */
    public function delete(string $id): array
    {
        $id = $this->requireNonEmpty($id, 'Please use a valid test id or WebDriver session id');

        return $this->request(new Request('DELETE', 'tests/' . $id));
    }

    /**
     * Stop a running test.
     *
     * @return array<mixed>
     */
    public function stop(string $id): array
    {
        $id = $this->requireNonEmpty($id, 'Please use a valid test id or WebDriver session id');

        return $this->request(new Request('PUT', 'tests/' . $id . '/stop'));
    }
}
