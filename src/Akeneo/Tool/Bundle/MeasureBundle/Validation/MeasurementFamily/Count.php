<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;

class Count extends Constraint
{
    public const MAX_MESSAGE = 'pim_measurements.validation.measurement_family.should_contain_max_elements';

    public function validatedBy()
    {
        return 'akeneo_measurement.validation.measurement_family.count';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
