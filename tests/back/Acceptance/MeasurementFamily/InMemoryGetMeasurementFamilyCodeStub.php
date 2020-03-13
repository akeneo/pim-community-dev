<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\GetMeasurementFamilyCodeInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryGetMeasurementFamilyCodeStub implements GetMeasurementFamilyCodeInterface
{
    /** @var MeasurementFamilyCode */
    private $measurementFamilyCode;

    public function forUnitCode(UnitCode $unitCode): MeasurementFamilyCode
    {
        if (null === $this->measurementFamilyCode) {
            throw new MeasurementFamilyNotFoundException();
        }

        return $this->measurementFamilyCode;
    }

    public function stubWith(MeasurementFamilyCode $measurementFamilyCode): void
    {
        $this->$measurementFamilyCode = $measurementFamilyCode;
    }
}
