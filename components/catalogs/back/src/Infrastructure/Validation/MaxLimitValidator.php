<?php

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Domain\Persistence\IsCatalogsNumberLimitReachedQueryInterface;
use Akeneo\Catalogs\Infrastructure\Service\GetCatalogsNumberLimit;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaxLimitValidator extends ConstraintValidator
{
    public function __construct(
        private IsCatalogsNumberLimitReachedQueryInterface $isCatalogsNumberLimitReachedQuery,
        private GetCatalogsNumberLimit $getCatalogsNumberLimit,
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MaxLimit) {
            throw new UnexpectedTypeException($constraint, MaxLimit::class);
        }

        if($this->isCatalogsNumberLimitReachedQuery->execute($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ number }}', $this->getCatalogsNumberLimit->getLimit())
                ->addViolation();
        }
    }
}
