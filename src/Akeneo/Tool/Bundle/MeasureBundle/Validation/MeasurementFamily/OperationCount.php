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

class OperationCount extends Constraint
{
    public $minMessage = 'pim_measurements.validation.measurement_family.convert.should_contain_min_elements';
    public $maxMessage = 'pim_measurements.validation.measurement_family.convert.should_contain_max_elements';

    public function validatedBy()
    {
        return 'akeneo_measurement.validation.measurement_family.operation_count';
    }
}
