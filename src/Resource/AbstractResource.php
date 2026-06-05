<?php

declare(strict_types=1);

namespace TestingBot\Resource;

use TestingBot\Configuration\ClientConfig;
use TestingBot\Exception\ApiException;
use TestingBot\Exception\InvalidArgumentException;
use TestingBot\Http\HttpClientInterface;
use TestingBot\Http\Request;

/**
 * Base class for API resources. Holds the transport + configuration and turns
 * a {@see Request} into a decoded array, throwing typed exceptions on failure.
 */
abstract class AbstractResource
{
    public function __construct(
        protected readonly HttpClientInterface $http,
        protected readonly ClientConfig $config,
    ) {
    }

    /**
     * Send a request and decode a successful (2xx) JSON response to an array.
     *
     * @return array<mixed>
     * @throws ApiException On any non-2xx response (mapped to the most specific
     *                      subclass).
     */
    protected function request(Request $request): array
    {
        $response = $this->http->send($request);

        if (!$response->isSuccessful()) {
            throw ApiException::fromResponse($response);
        }

        $decoded = $response->json();

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Wrap flat fields into the API's `prefix[field]` form
     * (e.g. `test[name]`, `user[first_name]`, `suite[name]`).
     *
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    protected function wrap(string $prefix, array $fields): array
    {
        $wrapped = [];
        foreach ($fields as $key => $value) {
            $wrapped[$prefix . '[' . $key . ']'] = $value;
        }

        return $wrapped;
    }

    /**
     * Guard that a string identifier is not empty before it is interpolated
     * into a request path.
     *
     * @throws InvalidArgumentException
     */
    protected function requireNonEmpty(string $value, string $message): string
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException($message);
        }

        return $value;
    }

    /**
     * Build a pagination query, merging any extra filters.
     *
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    protected function paginationQuery(int $offset, int $count, array $extra = []): array
    {
        return ['offset' => $offset, 'count' => $count] + $extra;
    }

    /**
     * Strip the `tb://` scheme from a storage app URL, leaving the bare app key.
     */
    protected function stripAppScheme(string $appUrl): string
    {
        return str_replace('tb://', '', $appUrl);
    }
}
