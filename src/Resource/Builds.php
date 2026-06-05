<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * Builds group related tests together.
 */
final class Builds extends AbstractResource
{
    /**
     * List builds, most recent first.
     *
     * @return array<mixed>
     */
    public function list(int $offset = 0, int $count = 10): array
    {
        return $this->request(new Request('GET', 'builds', query: $this->paginationQuery($offset, $count)));
    }

    /**
     * List the tests that belong to a build (by numeric id or build identifier).
     *
     * @return array<mixed>
     */
    public function getTests(string $id, int $offset = 0, int $count = 10): array
    {
        $id = $this->requireNonEmpty($id, 'Please use a valid build id');

        return $this->request(new Request('GET', 'builds/' . $id, query: $this->paginationQuery($offset, $count)));
    }

    /**
     * Delete a build and all of its tests and assets.
     *
     * @return array<mixed>
     */
    public function delete(string $id): array
    {
        $id = $this->requireNonEmpty($id, 'Please use a valid build id');

        return $this->request(new Request('DELETE', 'builds/' . $id));
    }
}
