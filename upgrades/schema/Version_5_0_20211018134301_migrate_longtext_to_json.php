<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Catalogs created in 3.0 (and older ones) have columns created with longtext type. See PIM-10040.
 * To be consistent with new catalogs we migrate them in json.
 */
final class Version_5_0_20211018134301_migrate_longtext_to_json extends AbstractMigration
{
    private array $columnsToMigrate = [
        [
            'table' => 'oro_user',
            'column' => 'product_grid_filters',
            'null' => true,
        ],
        [
            'table' => 'oro_user',
            'column' => 'properties',
            'null' => false,
        ],
        [
            'table' => 'akeneo_batch_job_execution',
            'column' => 'raw_parameters',
            'null' => false,
        ],
    ];

    public function getDescription() : string
    {
        return 'Migrate doctrine\'s json_array type from longtext to json.';
    }

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();

        $this->cleanupJobExecutionEmptyRawParameters();

        foreach ($this->columnsToMigrate as $column) {
            if ($this->columnNeedsToBeMigrated($column)) {
                $this->migrateColumnToJson($column);
            }
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function columnNeedsToBeMigrated(array $column): bool
    {
        $sql = <<<SQL
        SELECT DATA_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = :db_name AND table_name = :table_name AND column_name = :column_name
        SQL;

        $statement = $this->connection->executeQuery($sql, [
            'db_name' => $this->connection->getParams()['dbname'],
            'table_name' => $column['table'],
            'column_name' => $column['column'],
        ]);

        $columnType = $statement->fetchOne();

        return is_string($columnType) && \strtolower($columnType) !== 'json';
    }

    private function migrateColumnToJson(array $column): void
    {
        $sql = strtr('ALTER TABLE {table_name} MODIFY COLUMN {column_name} json {not_null} {default}', [
            '{table_name}' => $column['table'],
            '{column_name}' => $column['column'],
            '{not_null}' => $column['null'] ? '' : 'NOT NULL',
            '{default}' => $column['default'] ?? '',
        ]);

        $this->addSql($sql);
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    protected function cleanupJobExecutionEmptyRawParameters(): void
    {
        $updateJobExcecution = <<< SQL
UPDATE akeneo_batch_job_execution
SET raw_parameters =  JSON_OBJECT()
WHERE raw_parameters = '';
SQL;
        $this->connection->executeQuery($updateJobExcecution);
    }
}
