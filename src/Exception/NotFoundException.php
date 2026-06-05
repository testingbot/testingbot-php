<?php

declare(strict_types=1);

namespace TestingBot\Exception;

/**
 * Thrown on HTTP 404 — the requested test, build, tunnel, device or other
 * resource does not exist.
 */
class NotFoundException extends ApiException
{
}
