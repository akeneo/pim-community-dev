<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        // TODO RAB-678: Implement storage validation
    }
}
