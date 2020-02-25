<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Persistence;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;

interface MeasurementFamilyRepositoryInterface
{
    public function all(): array;

    public function getByCode(MeasurementFamilyCode $measurementFamilyCode): MeasurementFamily;
}
