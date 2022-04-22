<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier\Validation;

use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongToAnotherSupplier;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueContributorEmailValidator extends ConstraintValidator
{
    public function __construct(private SupplierContributorsBelongToAnotherSupplier $supplierContributorsBelongToAnotherSupplier)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        $supplier = $this->context->getObject();
        if (! $supplier instanceof UpdateSupplier) {
            return;
        }

        $invalidEmails = ($this->supplierContributorsBelongToAnotherSupplier)($supplier->identifier, [$value]);
        if(count($invalidEmails) > 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ emailAddress }}', $invalidEmails[0])
                ->addViolation();
        }
    }
}
