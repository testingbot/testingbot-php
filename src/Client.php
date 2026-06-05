<?php

declare(strict_types=1);

namespace TestingBot;

use TestingBot\Configuration\ClientConfig;
use TestingBot\Exception\ApiException;
use TestingBot\Http\CurlHttpClient;
use TestingBot\Http\HttpClientInterface;
use TestingBot\Http\Request;
use TestingBot\Resource\Browsers;
use TestingBot\Resource\Builds;
use TestingBot\Resource\Configuration;
use TestingBot\Resource\Devices;
use TestingBot\Resource\Jobs;
use TestingBot\Resource\Lab;
use TestingBot\Resource\LabSuites;
use TestingBot\Resource\Screenshots;
use TestingBot\Resource\Storage;
use TestingBot\Resource\TeamManagement;
use TestingBot\Resource\Tests;
use TestingBot\Resource\Tunnels;
use TestingBot\Resource\User;

/**
 * Entry point for the TestingBot API. Resources are exposed as lazily created,
 * memoised accessors:
 *
 *     $client = new TestingBot\Client($key, $secret);
 *     $client->tests()->list();
 *     $client->storage()->uploadFile('/path/to/app.apk');
 *
 * All resource methods return decoded arrays on success and throw
 * {@see \TestingBot\Exception\TestingBotExceptionInterface} on failure.
 */
final class Client
{
    public const VERSION = '2.0.0';

    private readonly ClientConfig $config;
    private readonly HttpClientInterface $http;

    /** @var array<class-string, object> */
    private array $resources = [];

    /**
     * @param array<string, mixed> $options Recognised: base_url, timeout,
     *                                       connect_timeout, ssl_verify, user_agent.
     */
    public function __construct(
        string $key,
        string $secret,
        array $options = [],
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->config = ClientConfig::fromArray($key, $secret, $options);
        $this->http = $httpClient ?? new CurlHttpClient($this->config);
    }

    public function configuration(): Configuration
    {
        return $this->resource(Configuration::class);
    }

    public function browsers(): Browsers
    {
        return $this->resource(Browsers::class);
    }

    public function devices(): Devices
    {
        return $this->resource(Devices::class);
    }

    public function tests(): Tests
    {
        return $this->resource(Tests::class);
    }

    public function builds(): Builds
    {
        return $this->resource(Builds::class);
    }

    public function storage(): Storage
    {
        return $this->resource(Storage::class);
    }

    public function tunnels(): Tunnels
    {
        return $this->resource(Tunnels::class);
    }

    public function user(): User
    {
        return $this->resource(User::class);
    }

    public function teamManagement(): TeamManagement
    {
        return $this->resource(TeamManagement::class);
    }

    public function lab(): Lab
    {
        return $this->resource(Lab::class);
    }

    public function labSuites(): LabSuites
    {
        return $this->resource(LabSuites::class);
    }

    public function screenshots(): Screenshots
    {
        return $this->resource(Screenshots::class);
    }

    public function jobs(): Jobs
    {
        return $this->resource(Jobs::class);
    }

    /**
     * Compute the authentication hash used to share a test's public page.
     * Pure local MD5 — no API call is made.
     */
    public function getAuthenticationHash(?string $identifier = null): string
    {
        $base = $this->config->key . ':' . $this->config->secret;

        return $identifier !== null
            ? md5($base . ':' . $identifier)
            : md5($base);
    }

    public function getConfig(): ClientConfig
    {
        return $this->config;
    }

    /**
     * Low-level escape hatch: send a raw {@see Request} through the same
     * pipeline the resources use (auth, decoding, error mapping). Useful for
     * endpoints the client does not yet wrap.
     *
     * @return array<mixed>
     * @throws ApiException On any non-2xx response.
     */
    public function request(Request $request): array
    {
        $response = $this->http->send($request);

        if (!$response->isSuccessful()) {
            throw ApiException::fromResponse($response);
        }

        $decoded = $response->json();

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function resource(string $class): object
    {
        /** @var T */
        return $this->resources[$class] ??= new $class($this->http, $this->config);
    }
}
