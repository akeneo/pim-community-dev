<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Acceptance\Context;

use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * A specialized stateful context to deal with exceptions
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class ExceptionContext implements Context
{
    private ?\Exception $exceptionThrown = null;

    /**
     * @Then /^an exception is thrown with message "(.*)"$/
     */
    public function anExceptionIsThrownWithMessage(string $errorMessage)
    {
        Assert::eq($this->exceptionThrown->getMessage(), $errorMessage);
    }

    /**
     * @Then /^an exception is thrown$/
     */
    public function anExceptionIsThrown()
    {
        Assert::notNull($this->exceptionThrown, 'An exception was expected, but none was thrown.');
    }

    /**
     * @Then /^there is no exception thrown$/
     */
    public function assertThereIsNoExceptionThrown()
    {
        Assert::null(
            $this->exceptionThrown,
            sprintf(
                'There was no exception expected, but one was found with message "%s"',
                null !== $this->exceptionThrown ? $this->exceptionThrown->getMessage() : ''
            )
        );
    }

    public function setException(\Exception $exception): void
    {
        $this->exceptionThrown = $exception;
    }

    public function hasException(): bool
    {
        return null !== $this->exceptionThrown;
    }
}
