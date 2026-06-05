<?php

declare(strict_types=1);

namespace TestingBot\Exception;

/**
 * Thrown on HTTP 429 — too many requests. Exposes the `Retry-After` value
 * (in seconds) when the API provides one.
 */
class RateLimitException extends ApiException
{
    public function __construct(
        string $message,
        int $statusCode,
        string $responseBody = '',
        private readonly ?int $retryAfter = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $responseBody, $previous);
    }

    /**
     * Seconds to wait before retrying, from the `Retry-After` header, or null
     * if the API did not specify one.
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
