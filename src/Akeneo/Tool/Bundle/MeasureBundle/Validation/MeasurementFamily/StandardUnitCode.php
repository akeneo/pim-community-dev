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

class StandardUnitCode extends Constraint
{
    public const STANDARD_UNIT_CODE_SHOULD_EXIST = 'pim_measurements.validation.measurement_family.standard_unit_code_should_exist';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
