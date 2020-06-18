<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class LocaleCodeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (! preg_match('#[a-z]{2,3}_[a-z0-9_]{2,}#i', $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
