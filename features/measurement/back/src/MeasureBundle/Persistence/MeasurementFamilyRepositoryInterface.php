<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Persistence;

use AkeneoMeasureBundle\Exception\MeasurementFamilyNotFoundException;
use AkeneoMeasureBundle\Model\MeasurementFamily;
use AkeneoMeasureBundle\Model\MeasurementFamilyCode;

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
