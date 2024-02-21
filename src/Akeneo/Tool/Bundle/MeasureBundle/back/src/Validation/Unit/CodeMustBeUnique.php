<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\Unit;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CodeMustBeUnique extends Constraint
{
    public string $message = 'pim_measurements.validation.unit.code.must_be_unique';

    public function validatedBy(): string
    {
        return 'akeneo_measurement.validation.unit.code_must_be_unique';
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
