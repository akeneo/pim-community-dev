<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints\AssociationType;

use Symfony\Component\Validator\Constraint;

class ShouldNotBeTwoWayAndQuantified extends Constraint
{
    public $message = 'pim_structure.validation.association_type.cannot_be_quantified_and_two_way';

    public function validatedBy(): string
    {
        return 'pim_structure.validator.constraint.association_type.should_not_be_two_way_and_quantified';
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
