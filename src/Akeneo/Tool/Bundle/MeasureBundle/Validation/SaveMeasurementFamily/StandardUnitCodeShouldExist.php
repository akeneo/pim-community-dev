<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\SaveMeasurementFamily;

use Symfony\Component\Validator\Constraint;

class StandardUnitCodeShouldExist extends Constraint
{
    public const STANDARD_UNIT_CODE_SHOULD_EXIST_IN_THE_LIST_OF_UNITS = 'pim_measurements.validation.measurement_family.standard_unit_code.should_be_in_the_list_of_units';
    public const STANDARD_UNIT_CODE_IS_REQUIRED = 'pim_measurements.validation.measurement_family.standard_unit_code.is_required';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
