# TestingBot PHP Client

The official PHP client for the [TestingBot API](https://testingbot.com/support/api) —
cross-browser & device testing, screenshots, tunnels, app storage and codeless tests.

[![CI](https://github.com/testingbot/testingbot-php/actions/workflows/test.yml/badge.svg)](https://github.com/testingbot/testingbot-php/actions/workflows/test.yml)

## Requirements

- PHP 8.1 or newer
- The `curl`, `json` and `fileinfo` extensions (all standard)

## Installation

```bash
composer require testingbot/testingbot-php
```

## Getting started

Grab your key and secret from your
[TestingBot account](https://testingbot.com/members/user/edit) and create a client:

```php
$client = new TestingBot\Client($key, $secret);

$browsers = $client->browsers()->list();
$test     = $client->tests()->get($webdriverSessionId);
$client->tests()->update($webdriverSessionId, ['name' => 'login test', 'success' => true]);
```

The API is grouped into resources, accessed via methods on the client. Every
call returns the decoded JSON response as an array, and throws a typed exception
on failure (see [Error handling](#error-handling)).

### Configuration options

```php
$client = new TestingBot\Client($key, $secret, [
    'timeout'         => 90,   // request timeout in seconds
    'connect_timeout' => 30,
    'base_url'        => 'https://api.testingbot.com/v1/',
    'ssl_verify'      => true,
    'user_agent'      => 'my-app/1.0',
]);
```

## Resources

### Tests

```php
$client->tests()->list(0, 10, ['build' => 'ci-42']); // paginated, filterable
$client->tests()->get($sessionId);
$client->tests()->update($sessionId, ['name' => 'checkout', 'success' => false]);
// test_environment_id (required): the browser_id of the browser/device it ran on,
// from $client->browsers()->list()
$client->tests()->create(['name' => 'external result', 'test_environment_id' => $browserId, 'success' => true]);
$client->tests()->stop($sessionId);
$client->tests()->delete($sessionId);
```

### Builds

```php
$client->builds()->list(0, 10);
$client->builds()->getTests($buildId);
$client->builds()->delete($buildId);
```

### Storage

```php
$upload = $client->storage()->uploadFile('/path/to/app.apk');   // => ['app_url' => 'tb://...']
$client->storage()->uploadRemoteUrl('https://example.com/app.apk');
$client->storage()->replaceFile($upload['app_url'], '/path/to/new.apk');
$client->storage()->list(0, 10);
$client->storage()->get($upload['app_url']);
$client->storage()->delete($upload['app_url']);
```

### Tunnels

```php
$client->tunnels()->list();
$client->tunnels()->getActive();
$client->tunnels()->get($tunnelId);
$client->tunnels()->create();
$client->tunnels()->delete($tunnelId);
```

### User & team

```php
$client->user()->get();
$client->user()->keys();
$client->user()->update(['first_name' => 'Jane']);

$client->teamManagement()->get();                 // concurrency snapshot
$client->teamManagement()->listUsers(0, 25);
$client->teamManagement()->createUser(['email' => 'dev@acme.com', 'password' => '…']);
$client->teamManagement()->updateUser($userId, ['credits' => 100]);
$client->teamManagement()->resetUserKeys($userId);
```

### Browsers, devices & configuration

```php
use TestingBot\Enum\BrowserType;

$client->browsers()->list(BrowserType::Webdriver);
$client->devices()->list('android');
$client->devices()->available();
$client->devices()->get($deviceId);
$client->configuration()->ipRanges();             // no authentication required
```

### Screenshots

```php
$batch = $client->screenshots()->create('https://example.com', [1, 2, 3], [
    'resolution' => '1920x1080',
    'fullpage'   => true,
]);
$client->screenshots()->get($batch['id']);
$client->screenshots()->list(0, 10);
```

### Codeless (Lab) tests & suites

```php
$test = $client->lab()->createTest(['name' => 'smoke', 'url' => 'https://example.com']);
$client->lab()->setSteps($test['lab_test_id'], [
    ['order' => 0, 'cmd' => 'open',  'locator' => '/',    'value' => ''],
    ['order' => 1, 'cmd' => 'click', 'locator' => '#go',  'value' => ''],
]);
$client->lab()->setBrowsers($test['lab_test_id'], [12, 34]);
$job = $client->lab()->trigger($test['lab_test_id']);

$client->labSuites()->create(['name' => 'Regression']);
$client->labSuites()->addTests($suiteId, [1, 2, 3]);
$client->labSuites()->trigger($suiteId);
```

### Jobs (polling)

Trigger endpoints return a `job_id` you can poll:

```php
$job = $client->lab()->trigger($labTestId);
$final = $client->jobs()->waitForCompletion($job['job_id'], timeout: 300, interval: 5);
```

### Sharing

```php
$hash = $client->getAuthenticationHash($sessionId); // for building share links
```

## Error handling

Any non-2xx response throws a typed exception; transport failures throw
`NetworkException`. All of them implement `TestingBot\Exception\TestingBotExceptionInterface`.

```php
use TestingBot\Exception\AuthenticationException;
use TestingBot\Exception\NotFoundException;
use TestingBot\Exception\RateLimitException;
use TestingBot\Exception\ApiException;
use TestingBot\Exception\NetworkException;

try {
    $test = $client->tests()->get($sessionId);
} catch (NotFoundException $e) {
    // 404 — no such test
} catch (AuthenticationException $e) {
    // 401 / 403
} catch (RateLimitException $e) {
    sleep($e->getRetryAfter() ?? 60);
} catch (ApiException $e) {
    error_log($e->getStatusCode() . ': ' . $e->getResponseBody());
} catch (NetworkException $e) {
    // DNS / connection / timeout / TLS
}
```

## Migrating from 1.x

The 1.x class is still here and every method keeps its name and signature:

```php
$api = new TestingBot\TestingBotAPI($key, $secret);
$api->getJob($sessionId);
$api->getTunnels();
```

The one behavioural change in 2.0: **failures now throw** instead of returning
an array with an `error` key. Wrap calls in `try/catch` as shown above. From the
facade you can reach any new resource via `$api->client()`:

```php
$api->client()->screenshots()->create('https://example.com', [1], ['resolution' => '1280x1024']);
```

See [CHANGELOG.md](CHANGELOG.md) for the full list of changes.

## Development

```bash
composer install
composer test         # unit tests (no credentials needed)
composer phpstan      # static analysis (level 8)
composer cs-fix       # apply coding standards (PSR-12)
```

Integration tests hit the live API and are skipped unless `TB_KEY` and
`TB_SECRET` are set:

```bash
TB_KEY=… TB_SECRET=… composer test:integration
```

## Releasing

Releases are tag-driven and require **no tokens or secrets**. Packagist syncs
new versions from the repository through its GitHub integration, and the
release workflow uses the built-in `GITHUB_TOKEN`.

1. Bump `Client::VERSION` and add a `## [x.y.z]` section to `CHANGELOG.md`.
2. Tag and push:

   ```bash
   git tag 2.0.0
   git push origin 2.0.0
   ```

The [`Release`](.github/workflows/release.yml) workflow then verifies the tag
matches `Client::VERSION`, runs the checks, and publishes a GitHub Release with
notes taken from `CHANGELOG.md`. Packagist updates on its own once the tag exists.

## License

Apache 2.0 — see [LICENSE.APACHE2](LICENSE.APACHE2).
