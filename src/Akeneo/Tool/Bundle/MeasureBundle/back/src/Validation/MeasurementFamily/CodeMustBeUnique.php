<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CodeMustBeUnique extends Constraint
{
    public string $message = 'pim_measurements.validation.measurement_family.code.must_be_unique';

    public function validatedBy(): string
    {
        return 'akeneo_measure.validation.measurement_family.code_must_be_unique';
    }
}
