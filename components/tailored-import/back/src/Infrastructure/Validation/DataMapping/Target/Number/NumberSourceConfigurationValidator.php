<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Number;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\SourceConfiguration\DecimalSeparator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class NumberSourceConfigurationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NumberSourceConfiguration) {
            throw new UnexpectedTypeException($constraint, NumberSourceConfiguration::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'decimal_separator' => new DecimalSeparator(),
        ]));
    }
}
