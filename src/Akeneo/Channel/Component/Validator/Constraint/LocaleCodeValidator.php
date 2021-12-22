<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class LocaleCodeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (! preg_match('#^[a-z0-9_]*[a-z]{2,3}_[a-z0-9_]{2,}[a-z0-9_]*$#i', $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
