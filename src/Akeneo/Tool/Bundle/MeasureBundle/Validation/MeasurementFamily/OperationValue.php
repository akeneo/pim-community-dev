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

class OperationValue extends Constraint
{
    public const VALUE_SHOULD_BE_A_NUMBER_IN_A_STRING = 'pim_measurements.validation.measurement_family.convert.value_should_be_a_number_in_a_string';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
