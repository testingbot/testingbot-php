<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * The authenticated user's account.
 */
final class User extends AbstractResource
{
    /**
     * Get the authenticated user's profile and plan information.
     *
     * @return array<mixed>
     */
    public function get(): array
    {
        return $this->request(new Request('GET', 'user'));
    }

    /**
     * Get the account's API key and secret.
     *
     * @return array<mixed>
     */
    public function keys(): array
    {
        return $this->request(new Request('GET', 'user/keys'));
    }

    /**
     * Update the user's profile (e.g. `first_name`, `last_name`).
     *
     * @param array<string, mixed> $details
     * @return array<mixed>
     */
    public function update(array $details): array
    {
        return $this->request(new Request('PUT', 'user', body: $this->wrap('user', $details)));
    }
}
