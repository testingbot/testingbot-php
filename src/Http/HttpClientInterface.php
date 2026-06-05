<?php

declare(strict_types=1);

namespace TestingBot\Http;

use TestingBot\Exception\NetworkException;

/**
 * Transport seam for the client. The default implementation is
 * {@see CurlHttpClient}; tests inject a fake to assert request shaping without
 * touching the network.
 */
interface HttpClientInterface
{
    /**
     * Send the request and return the raw response.
     *
     * @throws NetworkException When the request fails at the transport level
     *                          (DNS, connection, timeout, TLS).
     */
    public function send(Request $request): Response;
}
