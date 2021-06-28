<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\tests\back\Acceptance\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
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
     * @Then There is no violation
     */
    public function thereIsNoViolation(): void
    {
        $violationMessages = [];
        foreach ($this->constraintViolationList->getIterator() as $constraintViolation) {
            $violationMessages[] = \sprintf('%s: %s', $constraintViolation->getPropertyPath(), $constraintViolation->getMessage());
        }

        Assert::count($violationMessages, 0, \sprintf(
            'Some violations were raised: %s',
            \implode(PHP_EOL, $violationMessages)
        ));
    }

    /**
     * @Then /^There is a violation with message: (?P<message>.*)$/
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
