<?php

declare(strict_types=1);

namespace TestingBot;

use TestingBot\Http\HttpClientInterface;
use TestingBot\Http\Request;

/**
 * Backward-compatible facade for the 1.x API surface.
 *
 * Every method from the 1.x client is preserved with the same name and
 * signature; each delegates to the new resource-oriented {@see Client}. New
 * code should prefer `TestingBot\Client` directly.
 *
 * BEHAVIOUR CHANGE IN 2.0: failed API calls now throw
 * {@see \TestingBot\Exception\TestingBotExceptionInterface} instead of
 * returning an array containing an `error` key. See CHANGELOG.md.
 *
 * @mixin Client
 */
class TestingBotAPI
{
    private Client $client;

    /**
     * @param array<string, mixed> $options Forwarded to {@see Client}: `base_url`,
     *                                       `timeout`, `connect_timeout`,
     *                                       `ssl_verify`, `user_agent`.
     */
    public function __construct($key, $secret, array $options = [], ?HttpClientInterface $httpClient = null)
    {
        $this->client = new Client((string) $key, (string) $secret, $options, $httpClient);
    }

    /**
     * The underlying modern client, for access to resources not exposed as
     * flat methods (screenshots, team management, codeless suites, …).
     */
    public function client(): Client
    {
        return $this->client;
    }

    // -- Tests (jobs) --------------------------------------------------------

    /** @param array<string, mixed> $details @return array<mixed> */
    public function updateJob($sessionID, array $details)
    {
        return $this->client->tests()->update((string) $sessionID, $details);
    }

    /** @param array<string, mixed> $details @return array<mixed> */
    public function getJob($sessionID, array $details = [])
    {
        return $this->client->tests()->get((string) $sessionID, $details);
    }

    /** @param array<string, mixed> $extraOptions @return array<mixed> */
    public function getJobs($offset = 0, $count = 10, array $extraOptions = [])
    {
        return $this->client->tests()->list((int) $offset, (int) $count, $extraOptions);
    }

    /** @return array<mixed> */
    public function deleteJob($sessionID)
    {
        return $this->client->tests()->delete((string) $sessionID);
    }

    /** @return array<mixed> */
    public function stopJob($sessionID)
    {
        return $this->client->tests()->stop((string) $sessionID);
    }

    // -- Codeless (lab) tests ------------------------------------------------

    /** @param array<string, mixed> $extras @return array<mixed> */
    public function createLabTest($name, $baseUrl, $extras = [])
    {
        return $this->client->lab()->createTest(['name' => $name, 'url' => $baseUrl] + (array) $extras);
    }

    /**
     * Replace a codeless test's steps.
     *
     * Sends the exact legacy `steps[][...]` body for byte-for-byte
     * compatibility. New code should use `client()->lab()->setSteps()`, which
     * URL-encodes step values.
     *
     * @param array<int, array{cmd?: string, locator?: string, value?: string}> $steps
     * @return array<mixed>
     */
    public function modifyLabTestSteps($labTestID, array $steps)
    {
        $processedSteps = [];
        foreach (array_values($steps) as $i => $step) {
            $processedSteps[] = 'steps[][order]=' . $i
                . '&steps[][cmd]=' . ($step['cmd'] ?? '')
                . '&steps[][locator]=' . ($step['locator'] ?? '')
                . '&steps[][value]=' . ($step['value'] ?? '');
        }

        return $this->client->request(
            new Request('POST', 'lab/' . $labTestID . '/steps', body: implode('&', $processedSteps)),
        );
    }

    // -- Builds --------------------------------------------------------------

    /** @return array<mixed> */
    public function getBuilds($offset = 0, $count = 10)
    {
        return $this->client->builds()->list((int) $offset, (int) $count);
    }

    /** @return array<mixed> */
    public function getBuild($buildID)
    {
        return $this->client->builds()->getTests((string) $buildID);
    }

    /** @return array<mixed> */
    public function deleteBuild($buildID)
    {
        return $this->client->builds()->delete((string) $buildID);
    }

    // -- Tunnels -------------------------------------------------------------

    /** @return array<mixed> */
    public function getTunnels()
    {
        return $this->client->tunnels()->list();
    }

    /** @return array<mixed> */
    public function deleteTunnel($tunnelID)
    {
        return $this->client->tunnels()->delete((int) $tunnelID);
    }

    // -- User ----------------------------------------------------------------

    /** @return array<mixed> */
    public function getUserInfo()
    {
        return $this->client->user()->get();
    }

    /** @param array<string, mixed> $details @return array<mixed> */
    public function updateUserInfo(array $details)
    {
        return $this->client->user()->update($details);
    }

    // -- Browsers & devices --------------------------------------------------

    /** @return array<mixed> */
    public function getBrowsers()
    {
        return $this->client->browsers()->list();
    }

    /** @return array<mixed> */
    public function getDevices()
    {
        return $this->client->devices()->list();
    }

    /** @return array<mixed> */
    public function getAvailableDevices()
    {
        return $this->client->devices()->available();
    }

    /** @return array<mixed> */
    public function getDevice($deviceID)
    {
        return $this->client->devices()->get((int) $deviceID);
    }

    // -- Storage -------------------------------------------------------------

    /** @return array<mixed> */
    public function uploadLocalFileToStorage($localFile)
    {
        return $this->client->storage()->uploadFile((string) $localFile);
    }

    /** @return array<mixed> */
    public function uploadRemoteFileToStorage($remoteFileUrl)
    {
        return $this->client->storage()->uploadRemoteUrl((string) $remoteFileUrl);
    }

    /** @return array<mixed> */
    public function getStorageFile($appUrl)
    {
        return $this->client->storage()->get((string) $appUrl);
    }

    /** @return array<mixed> */
    public function getStorageFiles($offset = 0, $count = 10)
    {
        return $this->client->storage()->list((int) $offset, (int) $count);
    }

    /** @return array<mixed> */
    public function deleteStorageFile($appUrl)
    {
        return $this->client->storage()->delete((string) $appUrl);
    }

    // -- Sharing -------------------------------------------------------------

    public function getAuthenticationHash($identifier = null): string
    {
        return $this->client->getAuthenticationHash($identifier === null ? null : (string) $identifier);
    }
}
