<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\SaveMeasurementFamily;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WhenUsedInAProductAttributeShouldBeAbleToUpdateOnlyLabelsAndSymbolAndAddUnits extends Constraint
{
    public const MEASUREMENT_FAMILY_UNIT_REMOVAL_NOT_ALLOWED = 'pim_measurements.validation.measurement_family.measurement_family_units_is_locked_for_updates';
    public const MEASUREMENT_FAMILY_OPERATION_UPDATE_NOT_ALLOWED = 'pim_measurements.validation.measurement_family.measurement_family_unit_operations_locked_for_updates';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_measurement.validation.save_measurement_family.when_used_in_a_product_attribute_should_be_able_to_update_only_labels_and_symbol_and_add_units';
    }
}
