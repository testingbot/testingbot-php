<?php

declare(strict_types=1);

namespace TestingBot\Configuration;

use TestingBot\Client;
use TestingBot\Exception\InvalidArgumentException;

/**
 * Immutable configuration shared by the client, its resources and the
 * transport. Build with {@see ClientConfig::fromArray()}.
 */
final class ClientConfig
{
    public const DEFAULT_BASE_URL = 'https://api.testingbot.com/v1/';

    public function __construct(
        public readonly string $key,
        public readonly string $secret,
        public readonly string $baseUrl = self::DEFAULT_BASE_URL,
        public readonly int $connectTimeout = 30,
        public readonly int $timeout = 90,
        public readonly bool $sslVerify = true,
        public readonly string $userAgent = 'testingbot-php',
    ) {
    }

    /**
     * Build configuration from a key/secret pair and an options array.
     *
     * Recognised options: `base_url`, `connect_timeout`, `timeout`,
     * `ssl_verify`, `user_agent`.
     *
     * @param array<string, mixed> $options
     */
    public static function fromArray(string $key, string $secret, array $options = []): self
    {
        if ($key === '') {
            throw new InvalidArgumentException('Key is required to use the TestingBot API');
        }

        if ($secret === '') {
            throw new InvalidArgumentException('Secret is required to use the TestingBot API');
        }

        $baseUrl = isset($options['base_url']) ? (string) $options['base_url'] : self::DEFAULT_BASE_URL;
        // Endpoints are joined as baseUrl . relativePath, so the base must end with a slash.
        if (!str_ends_with($baseUrl, '/')) {
            $baseUrl .= '/';
        }

        return new self(
            key: $key,
            secret: $secret,
            baseUrl: $baseUrl,
            connectTimeout: isset($options['connect_timeout']) ? (int) $options['connect_timeout'] : 30,
            timeout: isset($options['timeout']) ? (int) $options['timeout'] : 90,
            sslVerify: isset($options['ssl_verify']) ? (bool) $options['ssl_verify'] : true,
            userAgent: isset($options['user_agent'])
                ? (string) $options['user_agent']
                : sprintf('testingbot-php/%s (PHP %s)', Client::VERSION, PHP_VERSION),
        );
    }
}
