<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UpdateUserGroupAttributeGroupsPermissionsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UpdateUserGroupAttributeGroupsPermissions) {
            throw new UnexpectedTypeException($constraint, UpdateUserGroupAttributeGroupsPermissions::class);
        }

        $validations = $this->context->getValidator()->validate($value, $constraint->getConstraints());

        if ($validations->count() > 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
