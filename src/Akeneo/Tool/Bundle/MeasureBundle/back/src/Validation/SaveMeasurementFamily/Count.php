<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\SaveMeasurementFamily;

use Symfony\Component\Validator\Constraint;

class Count extends Constraint
{
    public const MAX_MESSAGE = 'pim_measurements.validation.measurement_family.should_contain_max_elements';

    public function validatedBy(): string
    {
        return 'akeneo_measurement.validation.save_measurement_family.count';
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
