<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier\Validation;

use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueContributorEmailValidator extends ConstraintValidator
{
    public function __construct(private SupplierContributorsBelongingToAnotherSupplier $supplierContributorsBelongingToAnotherSupplier)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        $supplier = $this->context->getObject();
        if (! $supplier instanceof UpdateSupplier) {
            return;
        }

        $contributorEmails = ($this->supplierContributorsBelongingToAnotherSupplier)($supplier->identifier, [$value]);
        if (0 < count($contributorEmails)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ emailAddress }}', $contributorEmails[0])
                ->addViolation();
        }
    }
}
