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
use Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\SqlIntegrationTestCase;

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
        $this->loadSomeMetrics();
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

    private function loadSomeMetrics(): void
    {
        $sql = <<<SQL
INSERT INTO `akeneo_measurement` (`code`, `labels`, `standard_unit`, `units`)
VALUES
	('Area', '{\"en_US\": \"Area\", \"fr_FR\": \"Surface\"}', 'SQUARE_MILLIMETER', '[{\"code\": \"SQUARE_MILLIMETER\", \"labels\": {\"en_US\": \"Square millimeter\", \"fr_FR\": \"Millimètre carré\"}, \"symbol\": \"mm²\", \"convert_from_standard\": [{\"value\": \"0.000001\", \"operator\": \"mul\"}]}, {\"code\": \"SQUARE_CENTIMETER\", \"labels\": {\"en_US\": \"Square centimeter\", \"fr_FR\": \"Centimètre carré\"}, \"symbol\": \"cm²\", \"convert_from_standard\": [{\"value\": \"0.0001\", \"operator\": \"mul\"}]}]'),
	('Binary', '{\"en_US\": \"Binary\", \"fr_FR\": \"Binaire\"}', 'BYTE', '[{\"code\": \"BIT\", \"labels\": {\"en_US\": \"Bit\", \"fr_FR\": \"Bit\"}, \"symbol\": \"b\", \"convert_from_standard\": [{\"value\": \"0.125\", \"operator\": \"mul\"}]}, {\"code\": \"BYTE\", \"labels\": {\"en_US\": \"Byte\", \"fr_FR\": \"Octet\"}, \"symbol\": \"B\", \"convert_from_standard\": [{\"value\": \"1\", \"operator\": \"mul\"}]}]');
SQL;
        $this->connection->executeQuery($sql);
    }
}
