# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

PHP client library for the [TestingBot REST API](https://testingbot.com/support/api), distributed via Composer as `testingbot/testingbot-php`. Targets **PHP 8.1+**, PSR-4 autoloaded under `TestingBot\` → `src/`.

There are two entry points:
- `TestingBot\Client` (`src/Client.php`) — the modern, resource-grouped client. New work goes here.
- `TestingBot\TestingBotAPI` (`src/TestingBotAPI.php`) — a thin backward-compatible facade that preserves the 1.x flat method names and delegates to `Client`.

## Commands

```bash
composer install
composer test            # unit suite — no credentials, no network (uses FakeHttpClient)
composer phpstan         # static analysis, level 8
composer cs-check        # PSR-12 dry-run; composer cs-fix to apply
composer test:integration   # live API; needs TB_KEY / TB_SECRET, otherwise skipped
vendor/bin/phpunit --filter testUpdateWrapsFields   # run a single test
```

PHP/Composer are provided via Homebrew (`/opt/homebrew/bin`); add it to `PATH` if `php` isn't found.

## Architecture

Request flow: a resource method builds a `Http\Request` value object and hands it to `AbstractResource::request()` (or `Client::request()` for the raw escape hatch), which sends it through an `Http\HttpClientInterface` and maps the `Http\Response` to a decoded array — or throws.

- **`Http\HttpClientInterface`** is the seam. `Http\CurlHttpClient` is the only class that touches cURL (auth, timeouts, TLS verify, User-Agent, `curl_errno` → `NetworkException`, status via `CURLINFO_RESPONSE_CODE`). Tests inject `tests/Support/FakeHttpClient.php`, which records the `Request` and replays canned `Response`s — this is why the unit suite needs no network. Inject a fake via the optional 4th constructor arg on both `Client` and `TestingBotAPI`.
- **Resources** (`src/Resource/*.php`) extend `AbstractResource` and each map to one API resource group. Helpers on the base class: `wrap('test', $fields)` for the `test[...]`/`user[...]`/`suite[...]` param convention, `stripAppScheme()` for `tb://` URLs, `paginationQuery()`, and `requireNonEmpty()` for id guards.
- **Errors throw.** `AbstractResource::request()` throws `ApiException::fromResponse()` on non-2xx, which selects the subclass by status (401/403 → `AuthenticationException`, 404 → `NotFoundException`, 429 → `RateLimitException`, else `ApiException`). All library throwables implement `Exception\TestingBotExceptionInterface`.
- **Multipart uploads** go through `Http\Multipart::fromFile()` (uses `ext-fileinfo`, not the removed `mime_content_type()`) and are sent through the same `CurlHttpClient` path as everything else — no separate curl handle.
- **The facade** keeps loose/untyped params on purpose so 1.x callers don't hit `TypeError`s; phpstan's `missingType` rule is ignored for `src/TestingBotAPI.php` only. `modifyLabTestSteps()` deliberately builds the legacy `steps[][...]` raw-string body via `Client::request()` for byte-for-byte compatibility — prefer `lab()->setSteps()` elsewhere.

When adding an endpoint: add a typed method on the relevant `Resource` class that builds a `Request` and returns `$this->request(...)`, add a `FakeHttpClient`-based unit test asserting the request shaping, and (if it was a 1.x method) a delegating method on the facade.

## Source of truth

The canonical API surface is the Grape definition at `/Users/jochen/projects/web/app/api/testingbot/api.rb`. All endpoints accept HTTP Basic auth (`key:secret`) and a generic `?omit=a,b,c` field filter; list endpoints return `{data, meta:{offset,count,total}}`.
