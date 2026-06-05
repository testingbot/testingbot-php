<?php

declare(strict_types=1);

namespace TestingBot\Exception;

/**
 * Thrown when arguments passed to a client method are invalid (e.g. an empty
 * session id or missing API credentials).
 *
 * Extends the SPL {@see \InvalidArgumentException} so existing `catch (\Exception)`
 * blocks keep working, while still being catchable via
 * {@see TestingBotExceptionInterface}.
 */
class InvalidArgumentException extends \InvalidArgumentException implements TestingBotExceptionInterface
{
}
