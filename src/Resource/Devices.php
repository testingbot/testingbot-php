<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * Real mobile devices in the TestingBot grid.
 */
final class Devices extends AbstractResource
{
    /**
     * List physical devices, optionally filtered by platform
     * (`android`, `ios`, `real_android`, `real_ios`).
     *
     * @return array<mixed>
     */
    public function list(?string $platform = null): array
    {
        $query = $platform !== null ? ['platform' => $platform] : [];

        return $this->request(new Request('GET', 'devices', query: $query));
    }

    /**
     * List devices the account can acquire right now.
     *
     * @return array<mixed>
     */
    public function available(): array
    {
        return $this->request(new Request('GET', 'devices/available'));
    }

    /**
     * Get a single device by its numeric id.
     *
     * @return array<mixed>
     */
    public function get(int $id): array
    {
        return $this->request(new Request('GET', 'devices/' . $id));
    }
}
