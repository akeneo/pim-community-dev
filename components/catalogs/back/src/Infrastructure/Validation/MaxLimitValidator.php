<?php

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Domain\Persistence\IsCatalogsNumberLimitReachedQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MaxLimitValidator extends ConstraintValidator
{
    public function __construct(
        private IsCatalogsNumberLimitReachedQueryInterface $isCatalogsNumberLimitReachedQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxLimit) {
            throw new UnexpectedTypeException($constraint, MaxLimit::class);
        }

        if ($this->isCatalogsNumberLimitReachedQuery->execute($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
