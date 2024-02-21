<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class InvalidJobException extends \RuntimeException
{
    public function __construct(
        string $jobCode,
        string $jobName,
        private ConstraintViolationListInterface $violations,
    ) {
        parent::__construct(sprintf(
            'Job instance "%s" running the job "%s" is invalid because of "%s"',
            $jobCode,
            $jobName,
            $this->formatViolations($violations)
        ));
    }

    private function formatViolations(ConstraintViolationListInterface $violations): string
    {
        $formattedViolations = '';

        foreach ($violations as $violation) {
            $formattedViolations .= sprintf('\n  - %s', $violation);
        }

        return $formattedViolations;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
