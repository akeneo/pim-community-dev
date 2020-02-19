<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\Persistence;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;

class MeasurementFamilyRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @var MeasurementFamilyRepositoryInterface */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_measure.persistence.measurement_family_repository');
    }

    /**
     * @test
     */
    public function it_returns_all_measurement_families()
    {
        $measurementFamilies = iterator_to_array($this->repository->all());

        $area = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Area'),
            LabelCollection::fromArray(["en_US" => "Area", "fr_FR" => "Surface"]),
            UnitCode::fromString('SQUARE_MILLIMETER'),
            [
                Unit::create(
                    UnitCode::fromString('SQUARE_MILLIMETER'),
                    LabelCollection::fromArray(["en_US" => "Square millimeter", "fr_FR" => "Millimètre carré"]),
                    [Operation::create("mul", "0.000001")],
                    "mm²",
                ),
                Unit::create(
                    UnitCode::fromString('SQUARE_CENTIMETER'),
                    LabelCollection::fromArray(["en_US" => "Square centimeter", "fr_FR" => "Centimètre carré"]),
                    [Operation::create("mul", "0.0001")],
                    "cm²",
                )
            ]
        );

        $this->assertEquals($area, $measurementFamilies[0]);
    }
}
