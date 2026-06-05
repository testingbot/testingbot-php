<?php

declare(strict_types=1);

namespace TestingBot\Exception;

/**
 * Thrown when the HTTP request never produced a response: DNS failure,
 * connection timeout, TLS handshake error, etc. Carries the underlying
 * cURL error number.
 */
class NetworkException extends TestingBotException
{
    public function __construct(
        string $message,
        private readonly int $curlErrno = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getCurlErrno(): int
    {
        return $this->curlErrno;
    }
}
