<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * Team / sub-account management for the team admin.
 */
final class TeamManagement extends AbstractResource
{
    /**
     * Get the team's concurrency snapshot (allowed vs current).
     *
     * @return array<mixed>
     */
    public function get(): array
    {
        return $this->request(new Request('GET', 'team-management'));
    }

    /**
     * List team members.
     *
     * @return array<mixed>
     */
    public function listUsers(int $offset = 0, int $count = 10): array
    {
        return $this->request(new Request('GET', 'team-management/users', query: $this->paginationQuery($offset, $count)));
    }

    /**
     * Get a single team member.
     *
     * @return array<mixed>
     */
    public function getUser(int $id): array
    {
        return $this->request(new Request('GET', 'team-management/users/' . $id));
    }

    /**
     * Get a team member's API client key.
     *
     * @return array<mixed>
     */
    public function getUserClientKey(int $id): array
    {
        return $this->request(new Request('GET', 'team-management/users/' . $id . '/client-key'));
    }

    /**
     * Rotate a team member's API credentials.
     *
     * @return array<mixed>
     */
    public function resetUserKeys(int $id): array
    {
        return $this->request(new Request('POST', 'team-management/users/' . $id . '/reset-keys'));
    }

    /**
     * Create a new sub-account.
     *
     * @param array<string, mixed> $details Fields: `email`, `password`,
     *        `first_name`, `last_name`, `concurrency`, `concurrencyPhysical`.
     * @return array<mixed>
     */
    public function createUser(array $details): array
    {
        return $this->request(new Request('POST', 'team-management/users', body: $details));
    }

    /**
     * Update a team member's profile and credit allocation.
     *
     * @param array<string, mixed> $details
     * @return array<mixed>
     */
    public function updateUser(int $id, array $details): array
    {
        return $this->request(new Request('PUT', 'team-management/users/' . $id, body: $details));
    }
}
