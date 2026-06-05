<?php

declare(strict_types=1);

namespace TestingBot\Exception;

use TestingBot\Http\Response;

/**
 * Thrown when the API returns a non-2xx HTTP status. Carries the HTTP status
 * code and the raw response body so callers can inspect the failure.
 *
 * Use {@see ApiException::fromResponse()} to build the most specific subclass
 * for a given response.
 */
class ApiException extends TestingBotException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly string $responseBody = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Build the most specific exception for a failed response:
     * 401/403 -> AuthenticationException, 404 -> NotFoundException,
     * 429 -> RateLimitException, anything else >= 400 -> ApiException.
     */
    public static function fromResponse(Response $response): self
    {
        $status = $response->getStatusCode();
        $body = $response->getRawBody();
        $message = self::extractMessage($response) ?? sprintf('TestingBot API request failed with HTTP %d', $status);

        return match (true) {
            $status === 401, $status === 403 => new AuthenticationException($message, $status, $body),
            $status === 404 => new NotFoundException($message, $status, $body),
            $status === 429 => new RateLimitException($message, $status, $body, self::retryAfter($response)),
            default => new self($message, $status, $body),
        };
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * The JSON-decoded response body, or null when the body was not valid JSON.
     *
     * @return array<mixed>|null
     */
    public function getDecoded(): ?array
    {
        if ($this->responseBody === '') {
            return null;
        }

        $decoded = json_decode($this->responseBody, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Pull a human-readable message out of the API's `{error: ...}` /
     * `{message: ...}` error envelope, if present.
     */
    private static function extractMessage(Response $response): ?string
    {
        $decoded = $response->json();
        if (!is_array($decoded)) {
            return null;
        }

        foreach (['error', 'message'] as $key) {
            if (isset($decoded[$key]) && is_string($decoded[$key]) && $decoded[$key] !== '') {
                return $decoded[$key];
            }
        }

        return null;
    }

    private static function retryAfter(Response $response): ?int
    {
        $value = $response->getHeader('retry-after');

        return $value !== null && is_numeric($value) ? (int) $value : null;
    }
}
