<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\Persistence;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
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
    private ?MeasurementFamilyRepositoryInterface $repository = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_measure.persistence.measurement_family_repository');
        $this->loadSomeMeasurements();
    }

    /**
     * @test
     */
    public function it_returns_all_measurement_families()
    {
        $measurementFamilies = $this->repository->all();

        $this->assertCount(2, $measurementFamilies);
        $this->assertEquals($this->createMeasurementFamily(), $measurementFamilies[0]);
    }

    /**
     * @test
     */
    public function it_returns_a_measurement_family_using_the_provided_code(): void
    {
        $measurementFamily = $this->repository->getByCode(MeasurementFamilyCode::fromString('Area'));

        $this->assertEquals($this->createMeasurementFamily(), $measurementFamily);
    }

    /**
     * @test
     */
    public function it_returns_the_amount_of_measurement_families_if_the_code_does_not_exists(): void
    {
        $this->assertEquals(2, $this->repository->countAllOthers(MeasurementFamilyCode::fromString('NOT_EXISTING')));
    }

    /**
     * @test
     */
    public function it_returns_the_amount_of_others_measurement_families_if_the_code_exists(): void
    {
        $this->assertEquals(1, $this->repository->countAllOthers(MeasurementFamilyCode::fromString('Area')));
    }

    /**
     * @test
     */
    public function it_throws_when_the_measurement_family_does_not_exists(): void
    {
        $this->expectException(MeasurementFamilyNotFoundException::class);

        $this->repository->getByCode(MeasurementFamilyCode::fromString('NOT_EXISTING'));
    }

    /**
     * @test
     */
    public function it_updates_an_existing_measurement_family_if_it_exists(): void
    {
        $area = $this->createMeasurementFamily('Area', ["en_US" => "New area label", "fr_FR" => "Nouveau surface label"]);
        $this->repository->save($area);

        $updatedArea = $this->repository->getByCode(MeasurementFamilyCode::fromString('Area'));
        $this->assertEquals($area, $updatedArea);
    }

    /**
     * @test
     */
    public function it_creates_an_new_measurement_family_if_the_code_is_not_present(): void
    {
        $measurementFamilies = $this->repository->all();
        $this->assertCount(2, $measurementFamilies);

        $newFamily = $this->createMeasurementFamily('NewFamily', ["en_US" => "New family label", "fr_FR" => "Nouveau famille label"]);
        $this->repository->save($newFamily);

        $newFamilyFetched = $this->repository->getByCode(MeasurementFamilyCode::fromString('NewFamily'));
        $this->assertEquals($newFamily, $newFamilyFetched);

        $measurementFamilies = $this->repository->all();
        $this->assertCount(3, $measurementFamilies);
    }

    /**
     * @test
     */
    public function it_deletes_a_measurement_family(): void
    {
        $measurementFamilies = $this->repository->all();
        $this->assertEquals(
            ['Area', 'Binary'],
            array_map(static fn (MeasurementFamily $measurementFamily) => $measurementFamily->normalize()['code'], $measurementFamilies)
        );

        $this->repository->deleteByCode(MeasurementFamilyCode::fromString('Area'));

        $measurementFamilies = $this->repository->all();
        $this->assertEquals(
            ['Binary'],
            array_map(static fn (MeasurementFamily $measurementFamily) => $measurementFamily->normalize()['code'], $measurementFamilies)
        );
    }

    /** @test */
    public function it_refreshes_the_cache_after_creating_a_measurement_family(): void
    {
        $this->assertCount(2, $this->repository->all());

        $this->repository->save(
            $this->createMeasurementFamily(
                'NewFamily',
                ["en_US" => "New family label", "fr_FR" => "Nouveau famille label"]
            )
        );

        $this->assertCount(3, $this->repository->all());
    }

    /**
     * @test
     */
    public function it_throws_when_the_measurement_family_being_removed_does_not_exists(): void
    {
        $this->expectException(MeasurementFamilyNotFoundException::class);

        $this->repository->deleteByCode(MeasurementFamilyCode::fromString('NOT_EXISTING'));
    }

    private function createMeasurementFamily(string $code = 'Area', array $labels = ["en_US" => "Area", "fr_FR" => "Surface"]): MeasurementFamily
    {
        return MeasurementFamily::create(
            MeasurementFamilyCode::fromString($code),
            LabelCollection::fromArray($labels),
            UnitCode::fromString('SQUARE_MILLIMETER'),
            [
                Unit::create(
                    UnitCode::fromString('SQUARE_MILLIMETER'),
                    LabelCollection::fromArray(["en_US" => "Square millimeter", "fr_FR" => "Millimètre carré"]),
                    [Operation::create("mul", "1")],
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
    }

    private function loadSomeMeasurements(): void
    {
        $sql = <<<SQL
TRUNCATE TABLE `akeneo_measurement`;
INSERT INTO `akeneo_measurement` (`code`, `labels`, `standard_unit`, `units`)
VALUES
	('Area', '{\"en_US\": \"Area\", \"fr_FR\": \"Surface\"}', 'SQUARE_MILLIMETER', '[{\"code\": \"SQUARE_MILLIMETER\", \"labels\": {\"en_US\": \"Square millimeter\", \"fr_FR\": \"Millimètre carré\"}, \"symbol\": \"mm²\", \"convert_from_standard\": [{\"value\": \"1\", \"operator\": \"mul\"}]}, {\"code\": \"SQUARE_CENTIMETER\", \"labels\": {\"en_US\": \"Square centimeter\", \"fr_FR\": \"Centimètre carré\"}, \"symbol\": \"cm²\", \"convert_from_standard\": [{\"value\": \"0.0001\", \"operator\": \"mul\"}]}]'),
	('Binary', '{\"en_US\": \"Binary\", \"fr_FR\": \"Binaire\"}', 'BYTE', '[{\"code\": \"BIT\", \"labels\": {\"en_US\": \"Bit\", \"fr_FR\": \"Bit\"}, \"symbol\": \"b\", \"convert_from_standard\": [{\"value\": \"1\", \"operator\": \"mul\"}]}, {\"code\": \"BYTE\", \"labels\": {\"en_US\": \"Byte\", \"fr_FR\": \"Octet\"}, \"symbol\": \"B\", \"convert_from_standard\": [{\"value\": \"1\", \"operator\": \"mul\"}]}]');
SQL;
        $this->connection->executeQuery($sql);
    }
}
