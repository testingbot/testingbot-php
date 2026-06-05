<?php

declare(strict_types=1);

namespace TestingBot\Http;

/**
 * An immutable description of an HTTP request to the TestingBot API.
 *
 * `path` is relative to the configured base URL (e.g. `tests/123`). The body
 * may be an array (form-urlencoded), a raw string, a {@see Multipart} body,
 * or null.
 */
final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed>|string|Multipart|null $body
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array|string|Multipart|null $body = null,
        public readonly array $headers = [],
        public readonly bool $requiresAuth = true,
    ) {
    }
}
