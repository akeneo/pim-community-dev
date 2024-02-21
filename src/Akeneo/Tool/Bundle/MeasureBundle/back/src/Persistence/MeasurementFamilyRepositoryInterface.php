<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Persistence;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;

interface MeasurementFamilyRepositoryInterface
{
    public function all(): array;

    /**
     * @throws MeasurementFamilyNotFoundException
     */
    public function getByCode(MeasurementFamilyCode $measurementFamilyCode): MeasurementFamily;

    public function save(MeasurementFamily $measurementFamily);

    public function countAllOthers(MeasurementFamilyCode $excludedMeasurementFamilyCode): int;

    public function clear(): void;

    /**
     * @throws MeasurementFamilyNotFoundException
     */
    public function deleteByCode(MeasurementFamilyCode $measurementFamilyCode);
}
