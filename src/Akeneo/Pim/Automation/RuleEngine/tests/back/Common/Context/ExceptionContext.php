<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Common\Context;

use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExceptionContext implements Context
{
    private static $exceptions = [];

    public static function addException(\Exception $exception): void
    {
        static::$exceptions[] = $exception;
    }

    /**
     * @BeforeScenario
     */
    public static function clean(): void
    {
        static::$exceptions = [];
    }

    /**
     * @Then /^no exception has been thrown$/
     */
    public function noExceptionHasBeenThrown(): void
    {
        Assert::count(static::$exceptions, 0, sprintf(
            'Some exceptions were thrown:%s%s',
            PHP_EOL,
            implode(PHP_EOL, $this->getExceptionMessages())
        ));
    }

    /**
     * @Then /^an exception has been thrown$/
     */
    public function anExceptionHasBeenThrown(): void
    {
        Assert::notEmpty(static::$exceptions, 'No exception was thrown.');
    }

    /**
     * @Then /^an exception with message "(?P<message>(?:[^"]|\\")*)" has been thrown$/
     */
    public function anExceptionWithMessageHasBeenThrown(string $message): void
    {
        $this->anExceptionHasBeenThrown();
        $message = str_replace('\\"', '"', $message);

        foreach (static::$exceptions as $exception) {
            if (strpos(trim($exception->getMessage()), trim($message)) !== false) {
                return;
            }
        }

        Assert::count(static::$exceptions, 0, sprintf(
            'The "%s" message is not found. The exception message(s) is:%s%s',
            $message,
            PHP_EOL,
            implode(PHP_EOL, $this->getExceptionMessages())
        ));
    }

    private function getExceptionMessages(): array
    {
        return array_map(function (\Exception $e) {
            return $e->getMessage();
        }, static::$exceptions);
    }
}
