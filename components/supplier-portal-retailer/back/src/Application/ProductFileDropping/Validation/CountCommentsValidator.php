<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Validation;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountProductFileComments;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CountCommentsValidator extends ConstraintValidator
{
    private const MAX_COMMENTS_PER_PRODUCT_FILE = 50;

    public function __construct(private CountProductFileComments $countProductFileComments)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        $commentProductFile = $this->context->getObject();

        if (
            self::MAX_COMMENTS_PER_PRODUCT_FILE <= ($this->countProductFileComments)(
                $commentProductFile->productFileIdentifier
            )
        ) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
