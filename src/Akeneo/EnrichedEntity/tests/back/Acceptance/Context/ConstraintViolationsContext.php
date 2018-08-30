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

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * A specialized stateful context to deal with constraint violations.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class ConstraintViolationsContext implements Context
{
    /** @var ConstraintViolationListInterface */
    private $violations;

    public function __construct()
    {
        $this->violations = new ConstraintViolationList();
    }

    /**
     * @Then /^there should be a validation error on the property \'([^\']*)\' with message \'([^\']*)\'$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyWithMessage(string $expectedPropertyPath, string $message): void
    {
        $this->assertThereShouldBeViolations();
        $this->assertViolationOnPropertyWithMesssage($expectedPropertyPath, $message);
    }

    /**
     * @Then /^there should be a validation error with message \'([^\']*)\'$/
     */
    public function thereShouldBeAValidationErrorWithMessage(string $message): void
    {
        $this->assertThereShouldBeViolations();
        $this->assertViolation($message);
    }

    public function addViolations(ConstraintViolationListInterface $violationList): void
    {
        $this->violations->addAll($violationList);
    }

    public function assertThereIsNoViolations(): void
    {
        if (0 !== $this->violations->count()) {
            Assert::assertTrue(
                false,
                sprintf('There should be no violations, but one was found with message "%s"',
                $this->violations->get(0)->getMessage())
            );
        }
    }

    public function assertThereShouldBeViolations(): void
    {
        Assert::assertGreaterThan(0, $this->violations->count(), 'There should be violations.');
    }

    public function assertViolationOnPropertyWithMesssage(string $expectedPropertyPath, string $expectedMessage): void
    {
        $found = false;
        foreach ($this->violations as $violation) {
            if ($expectedMessage === $violation->getMessage()
                && $expectedPropertyPath === $violation->getPropertyPath()
            ) {
                $found = true;
            }
        }

        $message = sprintf(
            'Expected violation on property "%s" with message "%s" not found.',
            $expectedPropertyPath,
            $expectedMessage
        );
        if ($this->hasViolations()) {
            $message = sprintf(
                'Expected violation on property "%s" with message "%s" not found. but one found with "%s"',
                $expectedPropertyPath,
                $expectedMessage,
                $this->violations->get(0)->getMessage()
            );
        }

        Assert::assertTrue(
            $found,
            $message
        );
    }

    public function assertViolation(string $expectedMessage): void
    {
        $found = false;
        foreach ($this->violations as $violation) {
            if ($expectedMessage === $violation->getMessage()) {
                $found = true;
            }
        }

        Assert::assertTrue($found, sprintf('Expected violation with message "%s" not found.', $expectedMessage));
    }

    public function hasViolations(): bool
    {
        return 0 < $this->violations->count();
    }
}
