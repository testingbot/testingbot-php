<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * TestingBot Tunnels (secure connections to local/staging environments).
 */
final class Tunnels extends AbstractResource
{
    /**
     * List active tunnels for the account.
     *
     * @return array<mixed>
     */
    public function list(): array
    {
        return $this->request(new Request('GET', 'tunnel/list'));
    }

    /**
     * Get the account's first active tunnel.
     *
     * @return array<mixed>
     */
    public function getActive(): array
    {
        return $this->request(new Request('GET', 'tunnel'));
    }

    /**
     * Get a tunnel by id.
     *
     * @return array<mixed>
     */
    public function get(int $id): array
    {
        return $this->request(new Request('GET', 'tunnel/' . $id));
    }

    /**
     * Launch a new tunnel.
     *
     * @param array<string, mixed> $options
     * @return array<mixed>
     */
    public function create(array $options = []): array
    {
        return $this->request(new Request('POST', 'tunnel/create', body: $options));
    }

    /**
     * Stop a tunnel by id.
     *
     * @return array<mixed>
     */
    public function delete(int $id): array
    {
        return $this->request(new Request('DELETE', 'tunnel/' . $id));
    }

    /**
     * Stop the account's active tunnel.
     *
     * @return array<mixed>
     */
    public function deleteActive(): array
    {
        return $this->request(new Request('DELETE', 'tunnel'));
    }
}
