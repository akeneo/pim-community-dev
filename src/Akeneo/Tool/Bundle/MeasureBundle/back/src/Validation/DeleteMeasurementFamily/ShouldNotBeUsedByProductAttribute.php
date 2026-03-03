<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\DeleteMeasurementFamily;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShouldNotBeUsedByProductAttribute extends Constraint
{
    public const MEASUREMENT_FAMILY_REMOVAL_NOT_ALLOWED = 'pim_measurements.validation.measurement_family.measurement_family_cannot_be_removed';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_measurement.validation.delete_measurement_family.should_not_be_used_by_product_attribute';
    }
}
