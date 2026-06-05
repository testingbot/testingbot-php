<?php

declare(strict_types=1);

namespace TestingBot\Http;

use TestingBot\Configuration\ClientConfig;
use TestingBot\Exception\NetworkException;

/**
 * cURL-backed transport. This is the only class in the library that talks to
 * cURL directly: it centralises authentication, timeouts, TLS verification,
 * the User-Agent, body encoding and transport-level error handling.
 */
final class CurlHttpClient implements HttpClientInterface
{
    public function __construct(private readonly ClientConfig $config)
    {
    }

    public function send(Request $request): Response
    {
        $handle = curl_init();

        /** @var array<string, string> $responseHeaders */
        $responseHeaders = [];

        $options = [
            CURLOPT_URL => $this->buildUrl($request),
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->config->connectTimeout,
            CURLOPT_TIMEOUT => $this->config->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->config->sslVerify,
            CURLOPT_SSL_VERIFYHOST => $this->config->sslVerify ? 2 : 0,
            CURLOPT_USERAGENT => $this->config->userAgent,
            CURLOPT_HTTPHEADER => $this->buildHeaders($request),
            CURLOPT_HEADERFUNCTION => function ($_handle, string $header) use (&$responseHeaders): int {
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return strlen($header);
            },
        ];

        if ($request->requiresAuth) {
            $options[CURLOPT_USERPWD] = $this->config->key . ':' . $this->config->secret;
        }

        $body = $this->resolveBody($request->body);
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($handle, $options);

        $rawBody = curl_exec($handle);

        if ($rawBody === false || curl_errno($handle) !== 0) {
            // The CurlHandle object is freed automatically when it goes out of
            // scope; curl_close() has been a no-op since PHP 8.0.
            throw new NetworkException(
                sprintf('Request to %s failed: %s', $request->path, curl_error($handle)),
                curl_errno($handle),
            );
        }

        /** @var int $status */
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

        return new Response($status, is_string($rawBody) ? $rawBody : '', $responseHeaders);
    }

    private function buildUrl(Request $request): string
    {
        $url = $this->config->baseUrl . ltrim($request->path, '/');

        if ($request->query !== []) {
            $url .= '?' . http_build_query($request->query);
        }

        return $url;
    }

    /**
     * @return list<string>
     */
    private function buildHeaders(Request $request): array
    {
        $headers = ['Accept: application/json'];
        foreach ($request->headers as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        return $headers;
    }

    /**
     * Encode the request body for cURL:
     *  - array      -> form-urlencoded string (application/x-www-form-urlencoded)
     *  - Multipart  -> field array (cURL switches to multipart/form-data)
     *  - string     -> sent verbatim
     *  - null       -> no body
     *
     * @param array<string, mixed>|string|Multipart|null $body
     * @return array<string, \CURLFile|string>|string|null
     */
    private function resolveBody(array|string|Multipart|null $body): array|string|null
    {
        if ($body === null) {
            return null;
        }

        if ($body instanceof Multipart) {
            return $body->getFields();
        }

        if (is_array($body)) {
            return http_build_query($body);
        }

        return $body;
    }
}
