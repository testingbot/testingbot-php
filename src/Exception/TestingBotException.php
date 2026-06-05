<?php

declare(strict_types=1);

namespace TestingBot\Exception;

/**
 * Base class for runtime failures raised by the TestingBot client.
 */
class TestingBotException extends \RuntimeException implements TestingBotExceptionInterface
{
}
