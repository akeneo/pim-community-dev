<?php

namespace Akeneo\Pim\WorkOrganization\ProductRevert\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationsException extends \InvalidArgumentException
{
    /** @var ConstraintViolationListInterface<ConstraintViolationInterface> */
    private $constraintViolations;

    public function __construct(ConstraintViolationListInterface $constraintViolations)
    {
        parent::__construct('Validation failed');

        $this->constraintViolations = $constraintViolations;
    }

    /**
     * @return ConstraintViolationListInterface<ConstraintViolationInterface>
     */
    public function getConstraintViolations(): ConstraintViolationListInterface
    {
        return $this->constraintViolations;
    }
}
