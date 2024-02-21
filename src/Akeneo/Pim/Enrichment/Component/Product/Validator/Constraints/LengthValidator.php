<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LengthValidator as BaseLengthValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LengthValidator extends BaseLengthValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Length) {
            throw new UnexpectedTypeException($constraint, Length::class);
        }
        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            return parent::validate($value, $constraint);
        }
        if (null === $value || ('' === $value && ($constraint->allowEmptyString ?? true))) {
            return parent::validate($value, $constraint);
        }
        if (null !== $constraint->normalizer) {
            return parent::validate($value, $constraint);
        }

        $stringValue = (string) $value;

        $length = strlen($stringValue);
        if (!$invalidCharset = !@mb_check_encoding($stringValue, $constraint->charset)) {
            $length = mb_strlen($stringValue, $constraint->charset);
        }

        if (null !== $constraint->max && $length > $constraint->max && $constraint->min !== $constraint->max) {
            $this->context->buildViolation($constraint->maxMessage)
                ->setParameter('%attribute%', $constraint->attributeCode)
                ->setParameter('%limit%', $constraint->max)
                ->setInvalidValue($value)
                ->setPlural((int) $constraint->max)
                ->setCode(Length::TOO_LONG_ERROR)
                ->addViolation();

            return;
        }

        return parent::validate($value, $constraint);
    }
}
