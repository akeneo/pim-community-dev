<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration updates conversion operation for ATM, PSI, TORR & MMHG pressure units
 */
final class Version_5_0_20201127102539_fix_pressure_operations extends AbstractMigration
{
    private const WRONG_VALUES = [
        'ATM' => '0.986923',
        'PSI' => '14.50376985373022',
        'TORR' => '750.06375541921',
        'MMHG' => '750.06375541921',
    ];

    private const CORRECT_VALUES = [
        'ATM' => '1.01325',
        'PSI' => '0.0689476',
        'TORR' => '0.00133322',
        'MMHG' => '0.00133322',
    ];

    public function up(Schema $schema): void
    {
        $selectPressureUnitsSql = <<<SQL
SELECT m.units
FROM `akeneo_measurement` m
WHERE m.code = 'Pressure';
SQL;

        $updatePressureUnitsSql = <<<SQL
UPDATE `akeneo_measurement` m
SET m.units = :units
WHERE m.code = 'Pressure';
SQL;

        $units = json_decode($this->connection->executeQuery($selectPressureUnitsSql)->fetchColumn(), true);
        $units = array_map('self::getCorrectedUnit', $units);

        $this->addSql($updatePressureUnitsSql, ['units' => json_encode($units)]);
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

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
