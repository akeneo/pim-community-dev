<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\CreateMeasurementFamily;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardUnitCodeShouldExist extends Constraint
{
    public const STANDARD_UNIT_CODE_SHOULD_EXIST_IN_THE_LIST_OF_UNITS = 'pim_measurements.validation.measurement_family.standard_unit_code.should_be_in_the_list_of_units';
    public const STANDARD_UNIT_CODE_IS_REQUIRED = 'pim_measurements.validation.measurement_family.standard_unit_code.is_required';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
