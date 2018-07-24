<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * A specialized statefull context to deal with exceptions
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class ExceptionContext implements Context
{
    /** @var \Exception */
    private $exceptionThrown = null;

    /**
     * @Then /^an exception is thrown with message "(.*)"$/
     */
    public function anExceptionIsThrownWithMessage(string $errorMessage)
    {
        Assert::eq($errorMessage, $this->exceptionThrown->getMessage());
    }

    public function setException(\Exception $exception): void
    {
        $this->exceptionThrown = $exception;
    }

    public function resetException(): void
    {
        $this->exceptionThrown = null;
    }
}
