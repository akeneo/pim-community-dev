<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20201127102539_fix_pressure_operations_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20201127102539_fix_pressure_operations';

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

    public function test_it_updates_the_pressure_units_with_the_correct_values_and_ignores_already_changed_values()
    {
        $units = $this->getPressureUnits();
        $unitCodes = array_column($units, 'code');

        // Setting the wrong value for ATM & TORR units here, these values should be corrected
        $units[array_search('ATM', $unitCodes)]['convert_from_standard'][0]['value'] = '0.986923';
        $units[array_search('TORR', $unitCodes)]['convert_from_standard'][0]['value'] = '750.06375541921';

        // Setting a custom value for PSI unit here, this value should be untouched
        $units[array_search('PSI', $unitCodes)]['convert_from_standard'][0]['value'] = '666';

        $this->updatePressureUnits($units);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertUnitsHaveCorrectConversionValues([
            'ATM' => '1.01325',
            'PSI' => '666',
            'TORR' => '0.00133322',
            'MMHG' => '0.00133322',
        ]);
    }

    private function getPressureUnits(): array
    {
        $selectPressureUnitsSql = <<<SQL
SELECT m.units
FROM `akeneo_measurement` m
WHERE m.code = 'Pressure';
SQL;

        return json_decode($this->connection->executeQuery($selectPressureUnitsSql)->fetchColumn(), true);
    }

    private function updatePressureUnits(array $units): void
    {
        $updatePressureUnitsSql = <<<SQL
        UPDATE `akeneo_measurement` m
        SET m.units = :units
        WHERE m.code = 'Pressure';
        SQL;

        $this->connection->executeQuery($updatePressureUnitsSql, ['units' => json_encode($units)]);;
    }

    private function assertUnitsHaveCorrectConversionValues(array $expectedUnitsConversionValues): void
    {
        $units = $this->getPressureUnits();

        foreach ($units as $unit) {
            if (isset($expectedUnitsConversionValues[$unit['code']])) {
                Assert::assertSame($expectedUnitsConversionValues[$unit['code']], $unit['convert_from_standard'][0]['value']);
            }
        }
    }
}
