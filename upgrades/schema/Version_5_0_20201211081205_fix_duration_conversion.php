<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201211081205_fix_duration_conversion extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $query = "SELECT standard_unit, units FROM akeneo_measurement WHERE code = 'Duration';";
        $duration = $this->connection->executeQuery($query)->fetch(\PDO::FETCH_ASSOC);

        if (null === $duration) {
            // The duration measurement was removed.
            $this->removeMigrationWarning();

            return;
        }

        if ('SECOND' !== $duration['standard_unit']) {
            // The conversion has already been change.
            $this->removeMigrationWarning();

            return;
        }

        $fixedUnits = array_map(
            function (array $unit): array {
                if ($unit['code'] === 'MONTH') {
                    $unit['convert_from_standard'][0]['value'] = '2628000';
                    $unit['convert_from_standard'][0]['operator'] = 'mul';
                }

                return $unit;
            },
            \json_decode($duration['units'], true)
        );

        $updateQuery = "UPDATE akeneo_measurement SET units = :units WHERE code = 'Duration';";
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
}
