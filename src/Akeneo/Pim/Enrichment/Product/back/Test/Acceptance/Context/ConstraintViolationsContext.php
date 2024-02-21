<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConstraintViolationsContext implements Context
{
    private ConstraintViolationListInterface $constraintViolationList;

    public function __construct()
    {
        $this->constraintViolationList = new ConstraintViolationList();
    }

    public function add(ConstraintViolationListInterface $constraintViolationList): void
    {
        $this->constraintViolationList->addAll($constraintViolationList);
    }

    /**
     * @BeforeScenario
     */
    public function clean(): void
    {
        $this->constraintViolationList = new ConstraintViolationList();
    }

    /**
     * @Then there is no violation
     */
    public function thereIsNoViolation(): void
    {
        $violationMessages = [];
        foreach ($this->constraintViolationList as $constraintViolation) {
            $violationMessages[] = \sprintf('%s: %s', $constraintViolation->getPropertyPath(), $constraintViolation->getMessage());
        }

        Assert::count($violationMessages, 0, \sprintf(
            'Some violations were raised: %s',
            \implode(PHP_EOL, $violationMessages)
        ));
    }

    /**
     * @Then /^there is a violation with message: (?P<message>.*)$/
     */
    public function thereIsAViolationWithMessage(string $message): void
    {
        $actualViolationMessages = [];

        foreach ($this->constraintViolationList as $constraintViolation) {
            if ($constraintViolation->getMessage() === $message) {
                return;
            }
            $actualViolationMessages[] = \sprintf('%s: %s', $constraintViolation->getPropertyPath(), $constraintViolation->getMessage());
        }

        if (empty($actualViolationMessages)) {
            throw new \RuntimeException(\sprintf(
                'No violation found with message "%s", no violation are raised',
                $message
            ));
        }

        throw new \RuntimeException(\sprintf(
            'No violation found with message "%s", actual messages are %s',
            $message,
            \implode(PHP_EOL, $actualViolationMessages)
        ));
    }
}
