<?php

namespace Akeneo\Channel\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class LocaleCodeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        // PIM-10212 For newly created locales, we do a proper check, for existing ones, we keep the old check to avoid BC breaks
        $locale = $this->context->getRoot();

        if (!$locale instanceof LocaleInterface) {
            return;
        }

        if (null === $locale->getId() && !preg_match('#^[a-z]{2,}_[a-z0-9_]{2,}$#i', $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();

            return;
        }

        if (! preg_match('#[a-z]{2,3}_[a-z0-9_]{2,}#i', $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
