<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Measurement;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\SourceParameter\DecimalSeparator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class MeasurementSourceParameterValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MeasurementSourceParameter) {
            throw new UnexpectedTypeException($constraint, MeasurementSourceParameter::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'unit' => new Unit($constraint->getFamilyCode()),
            'decimal_separator' => new DecimalSeparator(),
        ]));
    }
}
