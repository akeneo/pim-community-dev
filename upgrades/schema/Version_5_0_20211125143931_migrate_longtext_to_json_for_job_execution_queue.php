<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @todo @pull-up Do not pull-up this migration in master/6.0 (cf PIM-10179)
 */
final class Version_5_0_20211125143931_migrate_longtext_to_json_for_job_execution_queue extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if (true === $this->isMigrationNeeded()) {
            $sql = <<<SQL
            ALTER TABLE akeneo_batch_job_execution_queue MODIFY COLUMN options JSON;
            SQL;

            $this->addSql($sql);
        } else {
            $this->disableMigrationWarning();
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function isMigrationNeeded(): bool
    {
        $sql = <<<SQL
SELECT DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_schema = :db_name AND table_name = 'akeneo_batch_job_execution_queue' AND column_name = 'options';
SQL;

        $statement = $this->connection->executeQuery($sql, ['db_name' => $this->connection->getParams()['dbname']]);
        $columnType = $statement->fetch(\PDO::FETCH_COLUMN);

        return \is_string($columnType) && \strtolower($columnType) !== 'json';
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
