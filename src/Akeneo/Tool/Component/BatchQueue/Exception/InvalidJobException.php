<?php

namespace Akeneo\Tool\Component\BatchQueue\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidJobException extends \RuntimeException
{
    public function __construct(
        string $jobCode,
        string $jobName,
        ConstraintViolationListInterface $violations,
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
}
