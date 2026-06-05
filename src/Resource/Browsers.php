<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Enum\BrowserType;
use TestingBot\Http\Request;

/**
 * Supported browser environments.
 */
final class Browsers extends AbstractResource
{
    /**
     * List supported browsers, optionally filtered by automation protocol.
     *
     * @return array<mixed>
     */
    public function list(?BrowserType $type = null): array
    {
        $query = $type !== null ? ['type' => $type->value] : [];

        return $this->request(new Request('GET', 'browsers', query: $query));
    }
}
