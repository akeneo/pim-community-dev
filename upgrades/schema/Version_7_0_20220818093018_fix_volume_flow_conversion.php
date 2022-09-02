<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220818093018_fix_volume_flow_conversion extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $query = "SELECT standard_unit, units FROM akeneo_measurement WHERE code = 'VolumeFlow';";
        $volumeFlow = $this->connection->executeQuery($query)->fetchAssociative();

        if (null === $volumeFlow) {
            // The volume flow measurement was removed.
            $this->removeMigrationWarning();

            return;
        }

        if ('CUBIC_METER_PER_SECOND' !== $volumeFlow['standard_unit']) {
            // The conversion has already been change.
            $this->removeMigrationWarning();

            return;
        }

        $units = \json_decode($volumeFlow['units'], true);

        $fixedUnits = array_map(
            function (array $unit): array {
                if ($this->unitIsIncorrect($unit)) {
                    $unit = $this->fixUnit($unit);
                }

                return $unit;
            },
            $units
        );

        $updateQuery = "UPDATE akeneo_measurement SET units = :units WHERE code = 'VolumeFlow';";
        $this->addSql($updateQuery, ['units' => \json_encode($fixedUnits)]);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    protected function removeMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    protected function unitIsIncorrect(array $unit): bool
    {
        $wrongConversion = $this->findIncorrectConversionByCode($unit['code']);

        if (null === $wrongConversion) {
            return false;
        }

        // We only need to fix the unit if it's the original incorrect conversion
        return $wrongConversion == $unit['convert_from_standard'];
    }

    protected function findIncorrectConversionByCode(string $unitCode): ?array
    {
        $wrongUnits = [
            'LITER_PER_MINUTE' => [
                ['value' => '0.001', 'operator' => 'mul'],
                ['value' => '60', 'operator' => 'mul'],
            ],
            'LITER_PER_HOUR' => [
                ['value' => '0.001', 'operator' => 'mul'],
                ['value' => '3600', 'operator' => 'mul'],
            ],
            'LITER_PER_DAY' => [
                ['value' => '0.001', 'operator' => 'mul'],
                ['value' => '86400', 'operator' => 'mul'],
            ],
            'MILLILITER_PER_MINUTE' => [
                ['value' => '0.000001', 'operator' => 'mul'],
                ['value' => '60', 'operator' => 'mul'],
            ],
            'MILLILITER_PER_HOUR' => [
                ['value' => '0.000001', 'operator' => 'mul'],
                ['value' => '3600', 'operator' => 'mul'],
            ],
            'MILLILITER_PER_DAY' => [
                ['value' => '0.000001', 'operator' => 'mul'],
                ['value' => '86400', 'operator' => 'mul'],
            ]
        ];

        if (isset($wrongUnits[$unitCode])) {
            return $wrongUnits[$unitCode];
        }

        return null;
    }

    protected function getFixedConversionByCode(string $unitCode): array
    {
        $fixedUnits = [
            'LITER_PER_MINUTE' => [
                ['value' => '0.001', 'operator' => 'mul'],
                ['value' => '60', 'operator' => 'div'], // We fix time conversion here (per minute)
            ],
            'LITER_PER_HOUR' => [
                ['value' => '0.001', 'operator' => 'mul'],
                ['value' => '3600', 'operator' => 'div'], // We fix time conversion here (per hour)
            ],
            'LITER_PER_DAY' => [
                ['value' => '0.001', 'operator' => 'mul'],
                ['value' => '86400', 'operator' => 'div'], // We fix time conversion here (per day)
            ],
            'MILLILITER_PER_MINUTE' => [
                ['value' => '0.000001', 'operator' => 'mul'],
                ['value' => '60', 'operator' => 'div'], // We fix time conversion here (per minute)
            ],
            'MILLILITER_PER_HOUR' => [
                ['value' => '0.000001', 'operator' => 'mul'],
                ['value' => '3600', 'operator' => 'div'], // We fix time conversion here (per hour)
            ],
            'MILLILITER_PER_DAY' => [
                ['value' => '0.000001', 'operator' => 'mul'],
                ['value' => '86400', 'operator' => 'div'], // We fix time conversion here (per day)
            ]
        ];

        return $fixedUnits[$unitCode];
    }

    private function fixUnit(array $unit): array
    {
        $unit['convert_from_standard'] = $this->getFixedConversionByCode($unit['code']);

        return $unit;
    }
}
