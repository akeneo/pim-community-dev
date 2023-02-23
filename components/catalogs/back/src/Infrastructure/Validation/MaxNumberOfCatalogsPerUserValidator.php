<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\Catalog\IsCatalogsNumberLimitReachedQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MaxNumberOfCatalogsPerUserValidator extends ConstraintValidator
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

        if (!$value instanceof CreateCatalogCommand) {
            throw new \LogicException(
                \sprintf(
                    'MaxNumberOfCatalogsPerUserValidator can only be used on instances of "%s"',
                    CreateCatalogCommand::class,
                ),
            );
        }

        if ($this->isCatalogsNumberLimitReachedQuery->execute($value->getOwnerUsername())) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
