<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration updates conversion operation for microliter & microgram units
 */
final class Version_6_0_20210510102539_fix_micro_operations extends AbstractMigration
{
    private const WRONG_VALUES = [
        'MICROGRAM' => '0.000001',
        'MICROLITER' => '0.000001',
    ];

    private const CORRECT_VALUES = [
        'MICROGRAM' => '0.000000001',
        'MICROLITER' => '0.000000001',
    ];

    public function up(Schema $schema): void
    {
        $weightFixed = $this->fixMeasurementFamilyUnits('Weight');
        $volumeFixed = $this->fixMeasurementFamilyUnits('Volume');

        if (!$weightFixed && !$volumeFixed) {
            $this->removeMigrationWarning();
        }
    }

    private function fixMeasurementFamilyUnits(string $measurementFamilyCode): bool
    {
        $selectUnitsSql = <<<SQL
SELECT m.units
FROM `akeneo_measurement` m
WHERE m.code = :measurement_family_code;
SQL;

        $updateUnitsSql = <<<SQL
UPDATE `akeneo_measurement` m
SET m.units = :units
WHERE m.code = :measurement_family_code;
SQL;

        $existingUnits = $this->connection->executeQuery($selectUnitsSql, [
            'measurement_family_code' => $measurementFamilyCode
        ])->fetchOne();

        if (false === $existingUnits) {
            // The measurement family has been removed
            return false;
        }

        $units = json_decode($existingUnits, true);
        $units = array_map([$this, 'getCorrectedUnit'], $units);

        $this->addSql($updateUnitsSql, [
            'measurement_family_code' => $measurementFamilyCode,
            'units' => json_encode($units)
        ]);

        return true;
    }

    private function getCorrectedUnit(array $unit): array
    {
        $unitCode = $unit['code'];

        if (
            isset(self::WRONG_VALUES[$unitCode])
            && self::WRONG_VALUES[$unitCode] === $unit['convert_from_standard'][0]['value']
        ) {
            $unit['convert_from_standard'][0]['value'] = self::CORRECT_VALUES[$unitCode];
        }

        return $unit;
    }

    private function removeMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
