<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\ServiceApi;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily as MeasurementFamilyAggregate;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamily
{
    public string $code;
    public array $labels;
    public string $standardUnitCode;
    public array $units;

    public static function fromAggregate(MeasurementFamilyAggregate $measurementFamily): self
    {
        $normalizedMeasurementFamily = $measurementFamily->normalize();
        $readModel = new self();
        $readModel->code = $normalizedMeasurementFamily['code'];
        $readModel->labels = $normalizedMeasurementFamily['labels'];
        $readModel->standardUnitCode = $normalizedMeasurementFamily['standard_unit_code'];
        $readModel->units = $normalizedMeasurementFamily['units'];

        return $readModel;
    }
}
