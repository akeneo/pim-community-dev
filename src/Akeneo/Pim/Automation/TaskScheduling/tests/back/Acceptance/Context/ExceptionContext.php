<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\TaskScheduling\Acceptance\Context;

use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExceptionContext implements Context
{
    /** @var \Throwable[] */
    private static array $exceptions = [];

    public static function addException(\Throwable $exception): void
    {
        self::$exceptions[] = $exception;
    }

    /**
     * @BeforeScenario
     */
    public static function clean(): void
    {
        self::$exceptions = [];
    }

    /**
     * @Then no exception should have been thrown
     */
    public function noExceptionShouldHaveBeenThrown(): void
    {
        Assert::isEmpty(self::$exceptions);
    }
}
