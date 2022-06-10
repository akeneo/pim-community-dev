<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation;

use AkeneoTest\Platform\Acceptance\ImportExport\AcceptanceTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractValidationTest extends AcceptanceTestCase
{
    protected function assertHasValidationError(
        string $errorMessageExpected,
        string $propertyPathExpected,
        ConstraintViolationListInterface $violationList,
    ): void {
        $this->assertNotCount(0, $violationList, 'No violation found');
        $foundViolations = [];
        foreach ($violationList as $violation) {
            $foundViolations[$violation->getPropertyPath()][] = $violation->getMessage();
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
                implode(',', array_values($foundViolationMessages)),
            ),
        );
    }

    protected function assertNoViolation(ConstraintViolationListInterface $violationList): void
    {
        $propertyPathFound = array_map(fn ($violation) => $violation->getPropertyPath(), iterator_to_array($violationList));

        $this->assertCount(0, $violationList, sprintf('Violation list should be empty, found on following path "%s"', implode(', ', $propertyPathFound)));
    }

    protected function getValidator(): ValidatorInterface
    {
        return $this->get('validator');
    }
}
