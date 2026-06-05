# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0]

A full modernization of the client. The 1.x flat method surface is preserved
(see *Backward compatibility* below), but the internals, error handling and
minimum PHP version have changed.

### Added
- New resource-oriented entry point `TestingBot\Client` with grouped accessors:
  `tests()`, `builds()`, `storage()`, `tunnels()`, `user()`, `teamManagement()`,
  `lab()`, `labSuites()`, `screenshots()`, `devices()`, `browsers()`,
  `configuration()`, `jobs()`.
- Full API coverage: full test CRUD, user info/keys/update, team management
  (sub-accounts), codeless tests **and** suites, screenshots, tunnel
  create/get/list, devices, browsers, configuration IP ranges, and a job poller
  (`jobs()->waitForCompletion()`).
- Typed exception hierarchy: `AuthenticationException` (401/403),
  `NotFoundException` (404), `RateLimitException` (429, with `getRetryAfter()`),
  `ApiException` (other non-2xx, with `getStatusCode()`/`getResponseBody()`),
  and `NetworkException` (transport failures). All implement
  `TestingBot\Exception\TestingBotExceptionInterface`.
- A mockable transport seam (`TestingBot\Http\HttpClientInterface`) so the
  client can be unit-tested without hitting the live API.
- HTTP timeouts, explicit TLS verification, a versioned User-Agent, and
  transport-level error detection.
- `Client::request()` low-level escape hatch for endpoints not yet wrapped.
- `phpstan` (level 8), `php-cs-fixer` (PSR-12), `.editorconfig`, and a GitHub
  Actions matrix across PHP 8.1–8.4.

### Changed
- **Errors now throw exceptions** instead of returning an array containing an
  `error` key. Wrap calls in `try/catch` (see README "Migrating from 1.x").
- Minimum PHP version is now **8.1**.
- Autoloading moved from PSR-0 to **PSR-4** (the `TestingBot\TestingBotAPI`
  class name is unchanged).
- `composer.json` `type` corrected to `library`.
- `TestingBotAPI::createLabTest()` now wraps `$extras` keys as plain field
  names automatically (no need to pre-wrap them as `test[...]`).
- `TestingBotAPI::modifyLabTestSteps()` keeps sending the exact legacy body for
  compatibility; prefer `client()->lab()->setSteps()`, which URL-encodes values.

### Fixed
- File uploads no longer fatal on PHP 8: `mime_content_type()` replaced with the
  `fileinfo` extension.
- Network failures (DNS, timeout, TLS) are surfaced as `NetworkException`
  instead of being silently swallowed as `null`.

### Removed
- The bogus runtime dependencies `phpunit/phpunit-selenium`,
  `brianium/paratest` and `appium/php-client` (they were never used by the
  client and forced unnecessary installs).
- The obsolete `.travis.yml` (CI runs on GitHub Actions).

### Backward compatibility
All 1.x `TestingBotAPI` methods keep their names and signatures and continue to
work. The behavioural change to be aware of is that failures now throw instead
of returning `['error' => ...]`. Argument-validation now throws
`TestingBot\Exception\InvalidArgumentException`, a subclass of the SPL
`\InvalidArgumentException`, so existing `catch (\Exception $e)` blocks still
catch it.

## [1.0.3] and earlier

See the git history for changes prior to the 2.0 rewrite.
