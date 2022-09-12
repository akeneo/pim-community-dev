<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Validation;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountProductFileComments;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CountCommentsValidator extends ConstraintValidator
{
    public function __construct(private CountProductFileComments $countProductFileComments)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        $commentProductFile = $this->context->getObject();

        if (50 <= ($this->countProductFileComments)($commentProductFile->productFileIdentifier)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
