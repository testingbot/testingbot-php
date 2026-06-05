<?php

declare(strict_types=1);

namespace TestingBot\Enum;

/**
 * Automation protocol for the browsers list endpoint.
 */
enum BrowserType: string
{
    case Webdriver = 'webdriver';
    case Rc = 'rc';
}
