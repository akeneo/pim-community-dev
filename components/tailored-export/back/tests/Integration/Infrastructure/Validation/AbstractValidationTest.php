<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredExport\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractValidationTest extends IntegrationTestCase
{
    protected function assertHasValidationError(
        string $errorMessageExpected,
        string $propertyPathExpected,
        ConstraintViolationListInterface $violationList
    ): void {
        $this->assertNotCount(0, $violationList, 'No violation found');
        $foundViolations = [];
        foreach ($violationList as $violation) {
            $foundViolations[$violation->getPropertyPath()][] = $violation->getMessageTemplate();
        }

        $propertyPathFound = array_keys($foundViolations);
        $this->assertArrayHasKey(
            $propertyPathExpected,
            $foundViolations,
            sprintf(
                'No violation found at path "%s", found "%s"',
                $propertyPathExpected,
                implode(',', array_values($propertyPathFound)),
            ),
        );

        $foundViolationMessages = $foundViolations[$propertyPathExpected];
        $this->assertContains(
            $errorMessageExpected,
            $foundViolationMessages,
            sprintf(
                'Violation with text "%s" not found, found "%s"',
                $errorMessageExpected,
                implode(',', array_values($foundViolationMessages))
            )
        );
    }

    protected function assertNoViolation(ConstraintViolationListInterface $violationList): void
    {
        $propertyPathFound = array_map(
            static fn (ConstraintViolationInterface $violation) => $violation->getPropertyPath(),
            iterator_to_array($violationList),
        );

        $this->assertCount(0, $violationList, sprintf('Violation list should be empty, found on following path "%s"', implode(', ', $propertyPathFound)));
    }

    protected function getValidator(): ValidatorInterface
    {
        return $this->get('validator');
    }
}
