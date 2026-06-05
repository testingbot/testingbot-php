<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Multipart;
use TestingBot\Http\Request;

/**
 * Codeless ("Lab") tests — the scheduled, no-code tests created in the
 * TestingBot dashboard.
 */
final class Lab extends AbstractResource
{
    /**
     * List codeless tests.
     *
     * @return array<mixed>
     */
    public function listTests(int $offset = 0, int $count = 10): array
    {
        return $this->request(new Request('GET', 'lab', query: $this->paginationQuery($offset, $count)));
    }

    /**
     * Create a codeless test. When `$sideFile` is given (a Selenium IDE `.side`
     * export), the test fields travel alongside it as multipart form data.
     *
     * @param array<string, mixed> $details Fields: `name`, `url`, `cron`,
     *        `screenshot`, `video`, `idletimeout`, `screenresolution`, `ai_prompt`.
     * @return array<mixed>
     */
    public function createTest(array $details, ?string $sideFile = null): array
    {
        $wrapped = $this->wrap('test', $details);

        if ($sideFile === null) {
            return $this->request(new Request('POST', 'lab', body: $wrapped));
        }

        return $this->request(new Request('POST', 'lab', body: Multipart::fromFile($sideFile, 'file', $this->stringifyFields($wrapped))));
    }

    /**
     * Get a codeless test.
     *
     * @return array<mixed>
     */
    public function getTest(int $id): array
    {
        return $this->request(new Request('GET', 'lab/' . $id));
    }

    /**
     * Update a codeless test's metadata.
     *
     * @param array<string, mixed> $details
     * @return array<mixed>
     */
    public function updateTest(int $id, array $details): array
    {
        return $this->request(new Request('PUT', 'lab/' . $id, body: $this->wrap('test', $details)));
    }

    /**
     * Delete a codeless test.
     *
     * @return array<mixed>
     */
    public function deleteTest(int $id): array
    {
        return $this->request(new Request('DELETE', 'lab/' . $id));
    }

    /**
     * Run a codeless test now, optionally overriding the base URL for this run.
     *
     * @return array<mixed>
     */
    public function trigger(int $id, ?string $url = null): array
    {
        $body = $url !== null ? ['url' => $url] : [];

        return $this->request(new Request('POST', 'lab/' . $id . '/trigger', body: $body));
    }

    /**
     * Run all codeless tests now, optionally overriding the base URL.
     *
     * @return array<mixed>
     */
    public function triggerAll(?string $url = null): array
    {
        $body = $url !== null ? ['url' => $url] : [];

        return $this->request(new Request('POST', 'lab/trigger_all', body: $body));
    }

    /**
     * Stop a running codeless test, optionally only on a single browser.
     *
     * @return array<mixed>
     */
    public function stop(int $id, ?int $browserId = null): array
    {
        $body = $browserId !== null ? ['browser_id' => $browserId] : [];

        return $this->request(new Request('PUT', 'lab/' . $id . '/stop', body: $body));
    }

    /**
     * List a codeless test's steps.
     *
     * @return array<mixed>
     */
    public function getSteps(int $id, int $offset = 0, int $count = 10): array
    {
        return $this->request(new Request('GET', 'lab/' . $id . '/steps', query: $this->paginationQuery($offset, $count)));
    }

    /**
     * Replace all steps of a codeless test.
     *
     * @param list<array{order?: int, cmd?: string, locator?: string, value?: string}> $steps
     * @return array<mixed>
     */
    public function setSteps(int $id, array $steps): array
    {
        return $this->request(new Request('POST', 'lab/' . $id . '/steps', body: ['steps' => $steps]));
    }

    /**
     * Get the browsers a codeless test runs on.
     *
     * @return array<mixed>
     */
    public function getBrowsers(int $id): array
    {
        return $this->request(new Request('GET', 'lab/' . $id . '/browsers'));
    }

    /**
     * Set the browsers a codeless test runs on.
     *
     * @param list<int|string> $browserIds
     * @return array<mixed>
     */
    public function setBrowsers(int $id, array $browserIds): array
    {
        return $this->request(new Request('POST', 'lab/' . $id . '/browsers', body: ['browser_ids' => implode(',', $browserIds)]));
    }

    /**
     * Set a codeless test's schedule.
     *
     * @param array<string, mixed> $schedule Fields: `type`, `day`, `hour`, `cronFormat`.
     * @return array<mixed>
     */
    public function schedule(int $id, array $schedule): array
    {
        return $this->request(new Request('POST', 'lab/' . $id . '/schedule', body: $schedule));
    }

    /**
     * Add an alert to a codeless test.
     *
     * @param array<string, mixed> $alert Fields: `kind`, `level`, `content`.
     * @return array<mixed>
     */
    public function addAlert(int $id, array $alert): array
    {
        return $this->request(new Request('POST', 'lab/' . $id . '/alert', body: $alert));
    }

    /**
     * Update a codeless test's alert.
     *
     * @param array<string, mixed> $alert
     * @return array<mixed>
     */
    public function updateAlert(int $id, array $alert): array
    {
        return $this->request(new Request('PUT', 'lab/' . $id . '/alert', body: $alert));
    }

    /**
     * Add a daily report configuration to a codeless test.
     *
     * @param array<string, mixed> $report Fields: `email`, `cron`.
     * @return array<mixed>
     */
    public function addReport(int $id, array $report): array
    {
        return $this->request(new Request('POST', 'lab/' . $id . '/report', body: $report));
    }

    /**
     * Update a codeless test's report configuration.
     *
     * @param array<string, mixed> $report
     * @return array<mixed>
     */
    public function updateReport(int $id, array $report): array
    {
        return $this->request(new Request('PUT', 'lab/' . $id . '/report', body: $report));
    }

    /**
     * Flatten wrapped fields to strings for multipart transport.
     *
     * @param array<string, mixed> $fields
     * @return array<string, string>
     */
    private function stringifyFields(array $fields): array
    {
        $out = [];
        foreach ($fields as $key => $value) {
            if ($value === null) {
                continue;
            }
            $out[$key] = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        }

        return $out;
    }
}
