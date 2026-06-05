<?php

declare(strict_types=1);

namespace TestingBot\Exception;

/**
 * Thrown on HTTP 401/403 — missing/invalid API credentials, or the account
 * lacks permission for the requested operation.
 */
class AuthenticationException extends ApiException
{
}
