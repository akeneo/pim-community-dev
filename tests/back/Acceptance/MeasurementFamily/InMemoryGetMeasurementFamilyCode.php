<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\GetMeasurementFamilyCodeInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryGetMeasurementFamilyCode implements GetMeasurementFamilyCodeInterface
{
    /** @var InMemoryMeasurementFamilyRepository */
    private $inMemoryMeasurementFamilyRepository;

    public function __construct(InMemoryMeasurementFamilyRepository $inMemoryMeasurementFamilyRepository)
    {
        $this->inMemoryMeasurementFamilyRepository = $inMemoryMeasurementFamilyRepository;
    }

    public function forUnitCode(UnitCode $unitCode): MeasurementFamilyCode
    {
        $measurementFamilyContainingTheUnit = $this->measurementFamilyContainingUnitCode($unitCode);

        if (null === $measurementFamilyContainingTheUnit) {
            throw new MeasurementFamilyNotFoundException();
        }

        return $this->code($measurementFamilyContainingTheUnit);
    }

    private function measurementFamilyContainingUnitCode(UnitCode $unitCode): ?MeasurementFamily
    {
        $measurementFamilyContainingTheUnit = null;
        foreach ($this->inMemoryMeasurementFamilyRepository->all() as $measurementFamily) {
            foreach ($measurementFamily->normalize()['units'] as $unit) {
                if ($unitCode->normalize() === $unit['code']) {
                    $measurementFamilyContainingTheUnit = $measurementFamily;
                    break;
                }
            }
        }

        return $measurementFamilyContainingTheUnit;
    }

    private function code(MeasurementFamily $measurementFamilyContainingTheUnit): MeasurementFamilyCode
    {
        return MeasurementFamilyCode::fromString($measurementFamilyContainingTheUnit->normalize()['code']);
    }
}
