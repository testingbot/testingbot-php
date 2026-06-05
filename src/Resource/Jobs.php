<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Exception\TestingBotException;
use TestingBot\Http\Request;

/**
 * Asynchronous jobs created by trigger endpoints (e.g. running a codeless
 * test or suite). Poll one until it reaches a terminal state.
 */
final class Jobs extends AbstractResource
{
    /** Statuses that mean the job has settled. */
    private const TERMINAL = ['FINISHED', 'FAILED', 'DONE', 'ERROR', 'COMPLETED', 'STOPPED'];

    /**
     * Get a job's current status.
     *
     * @return array<mixed>
     */
    public function get(int $id): array
    {
        return $this->request(new Request('GET', 'jobs/' . $id));
    }

    /**
     * Poll a job until it reaches a terminal state or the timeout elapses.
     *
     * @param int $timeout  Maximum seconds to wait.
     * @param int $interval Seconds between polls.
     * @return array<mixed> The final job payload.
     * @throws TestingBotException When the timeout is reached first.
     */
    public function waitForCompletion(int $id, int $timeout = 120, int $interval = 3): array
    {
        $deadline = time() + $timeout;

        while (true) {
            $job = $this->get($id);
            $status = strtoupper((string) ($job['status'] ?? ''));

            if (in_array($status, self::TERMINAL, true)) {
                return $job;
            }

            if (time() >= $deadline) {
                throw new TestingBotException(sprintf('Timed out after %ds waiting for job %d to complete', $timeout, $id));
            }

            sleep(max(0, $interval));
        }
    }
}
