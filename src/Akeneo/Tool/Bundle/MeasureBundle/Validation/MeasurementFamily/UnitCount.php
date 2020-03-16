<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;

class UnitCount extends Constraint
{
    public const MAX_MESSAGE = 'pim_measurements.validation.measurement_family.units.should_contain_max_elements';
    public const MIN_MESSAGE = 'pim_measurements.validation.measurement_family.units.should_contain_at_least_one_unit';

    public function validatedBy()
    {
        return 'akeneo_measurement.validation.measurement_family.unit_count';
    }
}
