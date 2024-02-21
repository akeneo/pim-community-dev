<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\CreateMeasurementFamily;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardUnitCodeOperationShouldBeMultiplyByOne extends Constraint
{
    public const ERROR_MESSAGE = 'pim_measurements.validation.measurement_family.standard_unit_code.operation_should_be_multiply_by_one';

    public function getTargets(): string|array
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
