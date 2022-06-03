<?php

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Domain\Persistence\IsCatalogsNumberLimitReachedQueryInterface;
use Akeneo\Catalogs\Infrastructure\Service\GetCatalogsNumberLimit;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaxLimitPerUserValidator extends ConstraintValidator
{
    public function __construct(
        private IsCatalogsNumberLimitReachedQueryInterface $isCatalogsNumberLimitReachedQuery,
        private GetCatalogsNumberLimit $getCatalogsNumberLimit,
    ) {
    }

    public function validate($protocol, Constraint $constraint)
    {
        if (!$constraint instanceof MaxLimitPerUser) {
            throw new UnexpectedTypeException($constraint, MaxLimitPerUser::class);
        }

        if ($this->isCatalogsNumberLimitReachedQuery->execute($protocol->getOwnerId())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ number }}', $this->getCatalogsNumberLimit->getLimit())
                ->addViolation();
        }
    }
}
