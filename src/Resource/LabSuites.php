<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * Codeless test suites — named groups of Lab tests that run together.
 */
final class LabSuites extends AbstractResource
{
    /**
     * List codeless suites.
     *
     * @return array<mixed>
     */
    public function list(int $offset = 0, int $count = 10): array
    {
        return $this->request(new Request('GET', 'labsuites', query: $this->paginationQuery($offset, $count)));
    }

    /**
     * Create a codeless suite.
     *
     * @param array<string, mixed> $details Fields: `name`, `cron`, `screenshot`,
     *        `video`, `idletimeout`, `screenresolution`.
     * @return array<mixed>
     */
    public function create(array $details): array
    {
        return $this->request(new Request('POST', 'labsuites', body: $this->wrap('suite', $details)));
    }

    /**
     * Get a codeless suite.
     *
     * @return array<mixed>
     */
    public function get(int $id): array
    {
        return $this->request(new Request('GET', 'labsuites/' . $id));
    }

    /**
     * Delete a codeless suite.
     *
     * @return array<mixed>
     */
    public function delete(int $id): array
    {
        return $this->request(new Request('DELETE', 'labsuites/' . $id));
    }

    /**
     * List the tests in a suite.
     *
     * @return array<mixed>
     */
    public function getTests(int $id, int $offset = 0, int $count = 10): array
    {
        return $this->request(new Request('GET', 'labsuites/' . $id . '/tests', query: $this->paginationQuery($offset, $count)));
    }

    /**
     * Add tests to a suite.
     *
     * @param list<int|string> $testIds
     * @return array<mixed>
     */
    public function addTests(int $id, array $testIds): array
    {
        return $this->request(new Request('POST', 'labsuites/' . $id . '/tests', body: ['test_ids' => implode(',', $testIds)]));
    }

    /**
     * Remove a test from a suite.
     *
     * @return array<mixed>
     */
    public function removeTest(int $id, int $testId): array
    {
        return $this->request(new Request('DELETE', 'labsuites/' . $id . '/tests/' . $testId));
    }

    /**
     * Get the browsers a suite runs on.
     *
     * @return array<mixed>
     */
    public function getBrowsers(int $id): array
    {
        return $this->request(new Request('GET', 'labsuites/' . $id . '/browsers'));
    }

    /**
     * Set the browsers a suite runs on.
     *
     * @param list<int|string> $browserIds
     * @return array<mixed>
     */
    public function setBrowsers(int $id, array $browserIds): array
    {
        return $this->request(new Request('POST', 'labsuites/' . $id . '/browsers', body: ['browser_ids' => implode(',', $browserIds)]));
    }

    /**
     * Run every test in a suite now.
     *
     * @return array<mixed>
     */
    public function trigger(int $id): array
    {
        return $this->request(new Request('POST', 'labsuites/' . $id . '/trigger'));
    }
}
