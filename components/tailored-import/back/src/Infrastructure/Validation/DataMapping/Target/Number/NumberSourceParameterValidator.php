<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Number;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\SourceParameter\DecimalSeparator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class NumberSourceParameterValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NumberSourceParameter) {
            throw new UnexpectedTypeException($constraint, NumberSourceParameter::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'decimal_separator' => new DecimalSeparator(),
        ]));
    }
}
