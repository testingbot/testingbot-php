<?php

declare(strict_types=1);

namespace TestingBot\Exception;

/**
 * Marker interface implemented by every exception this library throws.
 *
 * Catching `TestingBotExceptionInterface` catches all client failures
 * (API errors, transport errors and argument-validation errors) in one place,
 * even though they extend different SPL base classes.
 */
interface TestingBotExceptionInterface extends \Throwable
{
}
