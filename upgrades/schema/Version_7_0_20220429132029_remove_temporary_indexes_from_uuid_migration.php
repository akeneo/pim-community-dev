<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220429132029_remove_temporary_indexes_from_uuid_migration extends AbstractMigration
{
    private const TEMPORARY_INDEX_NAME = 'migrate_to_uuid_temp_index_to_delete';

    public function getDescription(): string
    {
        return 'Remove temporary indexes and constraints created during the migration from id to uuid';
    }

    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();
        foreach (MigrateToUuidStep::TABLES as $tableName => $tableProperties) {
            $indexNamesToDelete = \array_keys($tableProperties[MigrateToUuidStep::TEMPORARY_INDEXES_INDEX]);
            $indexNamesToDelete[] = self::TEMPORARY_INDEX_NAME;
            // The addSql() method does not execute the query directly, it means the FK is not directly dropped
            // So we can try to drop the same FK twice => the second call fail. This variable helps to execute once.
            $fkIsAlreadyDropped = false;

            foreach ($indexNamesToDelete as $indexName) {
                if ($this->indexExists($tableName, $indexName)) {
                    if ($tableName === 'pim_catalog_product') {
                        $this->addSql(\strtr(
                            'ALTER TABLE {tableName} RENAME INDEX {indexName} TO {newIndexName};',
                            ['{tableName}' => $tableName, '{newIndexName}' => 'UNIQ_91CD19C0BF396750', '{indexName}' => $indexName]
                        ));
                    } else {
                        if (
                            $tableName === 'pim_data_quality_insights_product_criteria_evaluation'
                            && $this->fkExists('pim_data_quality_insights_product_criteria_evaluation', 'FK_dqi_product_criteria_evaluation')
                            && !$fkIsAlreadyDropped
                        ) {
                            $this->addSql(\strtr(
                                'ALTER TABLE {tableName} DROP FOREIGN KEY {foreignKeyName};',
                                ['{tableName}' => $tableName, '{foreignKeyName}' => 'FK_dqi_product_criteria_evaluation']
                            ));
                            $fkIsAlreadyDropped = true;
                        }
                        if (
                            $tableName === 'pim_data_quality_insights_product_score'
                            && $this->fkExists('pim_data_quality_insights_product_score', 'FK_dqi_product_score')
                            && !$fkIsAlreadyDropped
                        ) {
                            $this->addSql(\strtr(
                                'ALTER TABLE {tableName} DROP FOREIGN KEY {foreignKeyName};',
                                ['{tableName}' => $tableName, '{foreignKeyName}' => 'FK_dqi_product_score']
                            ));
                            $fkIsAlreadyDropped = true;
                        }
                        $this->addSql(\strtr(
                            'ALTER TABLE {tableName} DROP INDEX {indexName};',
                            ['{tableName}' => $tableName, '{indexName}' => $indexName]
                        ));
                    }
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT INDEX_NAME FROM information_schema.STATISTICS
                WHERE INDEX_SCHEMA = :schema AND TABLE_NAME = :table_name AND INDEX_NAME = :index_name
            ) as is_existing
        SQL;

        return (bool) $this->connection->executeQuery(
            $sql,
            [
                'schema' => $this->connection->getDatabase(),
                'table_name' => $tableName,
                'index_name' => $indexName,
            ]
        )->fetchOne();
    }

    private function fkExists(string $tableName, string $fkName): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT TABLE_SCHEMA, CONSTRAINT_NAME, TABLE_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                    WHERE TABLE_SCHEMA = :schema
                        AND TABLE_NAME = :table_name
                        AND CONSTRAINT_NAME = :fk_name
            ) as is_existing
        SQL;

        return (bool) $this->connection->executeQuery(
            $sql,
            [
                'schema' => $this->connection->getDatabase(),
                'table_name' => $tableName,
                'fk_name' => $fkName,
            ]
        )->fetchOne();
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
