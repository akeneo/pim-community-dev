<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CategoryCodeExistsValidator extends ConstraintValidator
{


    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CategoryCodeExists) {
            throw new UnexpectedTypeException($constraint, CategoryCodeExists::class);
        }

        if (empty($value)) {
            return;
        }

//                $this->context->buildViolation($constraint->message)->addViolation();

    }
}
