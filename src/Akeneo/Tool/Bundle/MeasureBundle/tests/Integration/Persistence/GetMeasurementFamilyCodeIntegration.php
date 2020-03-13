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

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\GetMeasurementFamilyCodeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\SqlIntegrationTestCase;

class GetMeasurementFamilyCodeIntegration extends SqlIntegrationTestCase
{
    /** @var GetMeasurementFamilyCodeInterface */
    private $getMeasurementFamilyCode;

    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyrepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->getMeasurementFamilyCode = $this->get('akeneo_measure.persistence.get_measurement_family_code');
        $this->measurementFamilyrepository = $this->get('akeneo_measure.persistence.measurement_family_repository');
    }

    /**
     * @test
     */
    public function it_gets_the_measurement_family_code_given_a_unit_code()
    {
        $expectedMeasurementFamilyCode = 'Area';
        $unitCodeToGetFrom = 'SQUARE_METER';
        $this->createMeasurementFamily($expectedMeasurementFamilyCode, $unitCodeToGetFrom);

        $actualMeasurementFamilyCode = $this->getMeasurementFamilyCode->forUnitCode(UnitCode::fromString($unitCodeToGetFrom));

        $this->assertEquals($expectedMeasurementFamilyCode, $actualMeasurementFamilyCode->normalize());
    }

    /**
     * @test
     */
    public function it_throws_if_there_is_no_measurement_family_having_the_unit_code()
    {
        $this->expectException(MeasurementFamilyNotFoundException::class);
        $this->getMeasurementFamilyCode->forUnitCode(UnitCode::fromString('unknown_unit_code'));
    }

    private function createMeasurementFamily(string $code, string $standardUnitCode): void
    {
        $this->measurementFamilyrepository->save(MeasurementFamily::create(
            MeasurementFamilyCode::fromString($code),
            LabelCollection::fromArray(["en_US" => "Area", "fr_FR" => "Surface"]),
            UnitCode::fromString($standardUnitCode),
            [
                Unit::create(
                    UnitCode::fromString($standardUnitCode),
                    LabelCollection::fromArray(["en_US" => "Square millimeter", "fr_FR" => "Millimètre carré"]),
                    [Operation::create("mul", "0.000001")],
                    "mm²",
                )
            ]
        ));
    }
}
