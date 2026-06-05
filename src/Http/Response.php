<?php

declare(strict_types=1);

namespace TestingBot\Http;

/**
 * An immutable HTTP response. Headers are stored lower-cased for
 * case-insensitive lookup.
 */
final class Response
{
    /** @var array<string, string> */
    private readonly array $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly string $rawBody,
        array $headers = [],
    ) {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = $value;
        }
        $this->headers = $normalized;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * The JSON-decoded body as an associative array/list, or null when the body
     * is empty or not valid JSON.
     */
    public function json(): mixed
    {
        if ($this->rawBody === '') {
            return null;
        }

        return json_decode($this->rawBody, true);
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }
}
