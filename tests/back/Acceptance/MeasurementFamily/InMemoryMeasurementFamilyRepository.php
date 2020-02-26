<?php

declare(strict_types=1);

namespace AkeneoTest\Acceptance\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryMeasurementFamilyRepository implements MeasurementFamilyRepositoryInterface
{
    /** @var MeasurementFamily[] */
    private $measurementFamilies = [];

    public function all(): array
    {
        if (empty($this->measurementFamilies)) {
            $this->measurementFamilies = $this->loadMeasurementFamilies();
        }

        return $this->measurementFamilies;
    }

    public function getByCode(MeasurementFamilyCode $measurementFamilyCode): MeasurementFamily
    {
        if (empty($this->measurementFamilies)) {
            $this->measurementFamilies = $this->loadMeasurementFamilies();
        }

        return $this->measurementFamilies[$measurementFamilyCode->normalize()];
    }

    private function loadMeasurementFamilies(): array
    {
        $frequency = MeasurementFamilyCode::fromString('Frequency');

        return [
            $frequency->normalize() => MeasurementFamily::create(
                $frequency,
                LabelCollection::fromArray(["en_US" => "Frequency", "fr_FR" => "Fréquence"]),
                UnitCode::fromString('MEGAHERTZ'),
                [
                    Unit::create(
                        UnitCode::fromString('MEGAHERTZ'),
                        LabelCollection::fromArray(["en_US" => "MEGAHERTZ"]),
                        [
                            Operation::create("mul", "1"),
                        ],
                        "mghz",
                    ),
                ]
            )
        ];
    }
}
