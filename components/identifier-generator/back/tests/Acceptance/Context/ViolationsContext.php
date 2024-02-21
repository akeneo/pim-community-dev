<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ViolationsContext implements Context
{
    private ?ViolationsException $violationsException = null;

    /**
     * @Then I should not get any error
     */
    public function iShouldNotGetAnyError(): void
    {
        Assert::null($this->violationsException, 'Errors were raised: ' . \json_encode($this->violationsException?->normalize()));
    }

    /**
     * @Then /^I should get an error with message '(?P<message>[^']*)'$/
     */
    public function iShouldGetAnErrorWithMessage(string $message): void
    {
        Assert::notNull($this->violationsException, 'No error were raised.');
        Assert::contains($this->violationsException->getMessage(), $message);
    }

    public function setViolationsException(ViolationsException $violationsException): void
    {
        $this->violationsException = $violationsException;
    }
}
