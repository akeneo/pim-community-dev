<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PasswordValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Password) {
            throw new UnexpectedTypeException($constraint, Password::class);
        }

        if (!is_string($value)) {
            return;
        }

        if (8 > strlen(trim($value))) {
            $this->context->buildViolation(Password::PASSWORD_MIN_LENGTH_MESSAGE)->addViolation();
        }

        if (255 < strlen(trim($value))) {
            $this->context->buildViolation(Password::PASSWORD_MAX_LENGTH_MESSAGE)->addViolation();
        }

        if (!preg_match('/(?=.*[A-Z])/', $value)) {
            $this->context->buildViolation(Password::PASSWORD_UPPERCASE_LETTER_MESSAGE)->addViolation();
        }

        if (!preg_match('/(?=.*[a-z])/', $value)) {
            $this->context->buildViolation(Password::PASSWORD_LOWERCASE_LETTER_MESSAGE)->addViolation();
        }

        if (!preg_match('/(?=.*\d)/', $value)) {
            $this->context->buildViolation(Password::PASSWORD_DIGIT_MESSAGE)->addViolation();
        }
    }
}
