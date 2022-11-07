<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;

class OperationValue extends Constraint
{
    public const VALUE_SHOULD_BE_A_NUMBER_IN_A_STRING = 'pim_measurements.validation.measurement_family.convert.value_should_be_a_number_in_a_string';

    public function getTargets(): string|array
    {
        return [self::PROPERTY_CONSTRAINT, self::CLASS_CONSTRAINT];
    }
}
