<?php
declare(strict_types=1);

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
