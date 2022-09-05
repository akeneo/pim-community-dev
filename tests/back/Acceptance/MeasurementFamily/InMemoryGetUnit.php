<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnit;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\Unit;

final class InMemoryGetUnit implements GetUnit
{
    public function __construct(private InMemoryMeasurementFamilyRepository $measurementFamilyRepository)
    {
    }

    public function byMeasurementFamilyCodeAndUnitCode(string $measurementFamilyCode, string $unitCode): Unit
    {
        try {
            $measurementFamily = $this->measurementFamilyRepository->getByCode(
                MeasurementFamilyCode::fromString($measurementFamilyCode)
            );
        } catch (MeasurementFamilyNotFoundException) {
            throw new \Exception(\sprintf('Unit code %s with family code %s was not found', $unitCode, $measurementFamilyCode));
        }

        $normalized = $measurementFamily->normalize();
        foreach ($normalized['units'] as $normalizedUnit) {
            if (\strtolower($normalizedUnit['code']) === \strtolower($unitCode)) {
                $unit = new Unit();
                $unit->code = $normalizedUnit['code'];
                $unit->labels = $normalizedUnit['labels'];
                $unit->convertFromStandard = $normalizedUnit['convert_from_standard'];
                $unit->symbol = $normalizedUnit['symbol'];

                return $unit;
            }
        }

        throw new \Exception(\sprintf('Unit code %s with family code %s was not found', $unitCode, $measurementFamilyCode));
    }
}
