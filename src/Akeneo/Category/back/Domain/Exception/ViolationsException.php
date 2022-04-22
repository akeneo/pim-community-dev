<?php

namespace Akeneo\Category\Domain\Exception;


use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ViolationsException extends \LogicException
{
    public function __construct(private ConstraintViolationListInterface $constraintViolationList)
    {
        parent::__construct(
            $this->constraintViolationList instanceof ConstraintViolationList
                ? (string)$this->constraintViolationList
                : 'Some violation(s) are raised'
        );
    }

    public function violations(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }

}
