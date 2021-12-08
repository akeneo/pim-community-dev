<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UpdateUserGroupCategoriesPermissionsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UpdateUserGroupCategoriesPermissions) {
            throw new UnexpectedTypeException($constraint, UpdateUserGroupCategoriesPermissions::class);
        }

        $validations = $this->context->getValidator()->validate($value, $constraint->getConstraints());

        if ($validations->count() > 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
