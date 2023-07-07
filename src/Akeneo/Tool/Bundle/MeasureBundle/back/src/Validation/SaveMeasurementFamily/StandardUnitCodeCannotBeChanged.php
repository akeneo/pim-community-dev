<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\SaveMeasurementFamily;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardUnitCodeCannotBeChanged extends Constraint
{
    public const ERROR_MESSAGE = 'pim_measurements.validation.measurement_family.standard_unit_code.cannot_be_changed';

    public function validatedBy(): string
    {
        return 'akeneo_measurement.validation.save_measurement_family.standard_unit_code_cannot_be_changed';
    }

    public function getTargets(): string|array
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
