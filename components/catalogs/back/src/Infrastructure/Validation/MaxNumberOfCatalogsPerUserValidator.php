<?php

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Domain\Persistence\IsCatalogsNumberLimitReachedQueryInterface;
use Akeneo\Catalogs\Domain\Validation\GetOwnerIdInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MaxNumberOfCatalogsPerUserValidator extends ConstraintValidator
{
    public function __construct(
        private IsCatalogsNumberLimitReachedQueryInterface $isCatalogsNumberLimitReachedQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxNumberOfCatalogsPerUser) {
            throw new UnexpectedTypeException($constraint, MaxNumberOfCatalogsPerUser::class);
        }

        if (!$value instanceof GetOwnerIdInterface) {
            throw new \LogicException('$value must implements components/catalogs/back/src/Domain/Validation/GetOwnerIdInterface.php');
        }

        if ($this->isCatalogsNumberLimitReachedQuery->execute($value->getOwnerId())) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
