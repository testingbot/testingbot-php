<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Http\Request;

/**
 * Account-independent configuration endpoints.
 */
final class Configuration extends AbstractResource
{
    /**
     * TestingBot's public IPv4 addresses, for firewall whitelisting.
     * No authentication required.
     *
     * @return list<string>
     */
    public function ipRanges(): array
    {
        /** @var list<string> $ranges */
        $ranges = $this->request(new Request('GET', 'configuration/ip-ranges', requiresAuth: false));

        return $ranges;
    }
}
