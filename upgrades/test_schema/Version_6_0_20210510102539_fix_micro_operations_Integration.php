<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20210510102539_fix_micro_operations_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210510102539_fix_micro_operations';

    private Connection $connection;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
    }

    public function test_it_updates_the_weight_and_volume_units_with_the_correct_values_and_ignores_already_changed_values()
    {
        $weightUnits = $this->getMeasurementFamilyUnits('Weight');
        $weightUnitCodes = array_column($weightUnits, 'code');

        $volumeUnits = $this->getMeasurementFamilyUnits('Volume');
        $volumeUnitCodes = array_column($volumeUnits, 'code');

        // Setting the wrong value for MICROGRAM unit here, these values should be corrected
        $weightUnits[array_search('MICROGRAM', $weightUnitCodes)]['convert_from_standard'][0]['value'] = '0.000001';

        // Setting a custom value for MICROLITER unit here, this value should be untouched
        $volumeUnits[array_search('MICROLITER', $volumeUnitCodes)]['convert_from_standard'][0]['value'] = '666';

        $this->updateMeasurementFamilyUnits('Weight', $weightUnits);
        $this->updateMeasurementFamilyUnits('Volume', $volumeUnits);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertUnitsHaveCorrectConversionValues('Weight', [
            'MICROGRAM' => '0.000000001',
        ]);
        $this->assertUnitsHaveCorrectConversionValues('Volume', [
            'MICROLITER' => '666',
        ]);
    }

    private function getMeasurementFamilyUnits(string $measurementFamilyCode): array
    {
        $selectPressureUnitsSql = <<<SQL
SELECT m.units
FROM `akeneo_measurement` m
WHERE m.code = :measurement_family_code;
SQL;

        return json_decode($this->connection->executeQuery($selectPressureUnitsSql, [
            'measurement_family_code' => $measurementFamilyCode
        ])->fetchOne(), true);
    }

    private function updateMeasurementFamilyUnits(string $measurementFamilyCode, array $units): void
    {
        $updateMeasurementFamilyUnitsSql = <<<SQL
        UPDATE `akeneo_measurement` m
        SET m.units = :units
        WHERE m.code = :measurement_family_code;
        SQL;

        $this->connection->executeQuery($updateMeasurementFamilyUnitsSql, [
            'measurement_family_code' => $measurementFamilyCode,
            'units' => json_encode($units)
        ]);;
    }

    private function assertUnitsHaveCorrectConversionValues(string $measurementFamilyCode, array $expectedUnitsConversionValues): void
    {
        $units = $this->getMeasurementFamilyUnits($measurementFamilyCode);

        foreach ($units as $unit) {
            if (isset($expectedUnitsConversionValues[$unit['code']])) {
                Assert::assertSame($expectedUnitsConversionValues[$unit['code']], $unit['convert_from_standard'][0]['value']);
            }
        }
    }
}
