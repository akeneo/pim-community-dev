<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnitCodesShouldBeUniqueAcrossMeasurementFamilies extends Constraint
{
    public const ERROR_MESSAGE = 'pim_measurements.validation.measurement_family.units.should_be_unique_across_measurement_families';

    public function validatedBy()
    {
        return 'akeneo_measurement.validation.measurement_family.units.unit_code_should_be_unique_across_measurement_families';
    }

    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
