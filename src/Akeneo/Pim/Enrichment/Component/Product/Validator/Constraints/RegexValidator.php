<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\RegexValidator as BaseValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RegexValidator extends BaseValidator
{
    /**
     * {@inheritDoc}
     *
     * Copy of Symfony RegexValidator to add custom violation parameters.
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Regex) {
            throw new UnexpectedTypeException($constraint, Regex::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string) $value;

        if (null !== $constraint->normalizer) {
            $value = ($constraint->normalizer)($value);
        }

        if ($constraint->match xor preg_match($constraint->pattern, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ pattern }}', $constraint->pattern)
                ->setParameter('{{ attribute_code }}', $constraint->attributeCode)
                ->setCode(Regex::REGEX_FAILED_ERROR)
                ->addViolation();
        }
    }
}
