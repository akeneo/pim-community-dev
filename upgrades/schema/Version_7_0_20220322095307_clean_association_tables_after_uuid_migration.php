<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220322095307_clean_association_tables_after_uuid_migration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clean association tables after uuid migration';
    }

    public function up(Schema $schema): void
    {
        $insertTriggerName = self::getInsertTriggerName('pim_catalog_association');
        $updateTriggerName = self::getUpdateTriggerName('pim_catalog_association');

        $sql = <<<SQL
        DROP TRIGGER IF EXISTS $insertTriggerName;
        DROP TRIGGER IF EXISTS $updateTriggerName;
        SQL;
        $this->addSql($sql);

        if ($this->constraintExists('pim_catalog_association', 'locale_foreign_key_idx')) {
            $this->addSql('ALTER TABLE pim_catalog_association DROP CONSTRAINT locale_foreign_key_idx, ALGORITHM=INPLACE, LOCK=NONE');
        }

        $columnsToRemove = [
            'pim_catalog_association' => 'owner_id',
            'pim_catalog_association_product' => 'product_id',
            'pim_catalog_association_product_model_to_product' => 'product_id',
        ];

        foreach ($columnsToRemove as $tableName => $columnName) {
            // We need to remove the FK before removing the column.
            // No need to remove the index, it will be removed automatically.
            $foreignKeyName = $this->getForeignKeyName($tableName, $columnName);
            if (null !== $foreignKeyName) {
                $this->addSql("ALTER TABLE $tableName DROP CONSTRAINT $foreignKeyName");
            }

            if ($this->columnExists($tableName, $columnName)) {
                $this->addSql("ALTER TABLE $tableName DROP COLUMN $columnName");
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr('SHOW COLUMNS FROM {table_name} LIKE :columnName', ['{table_name}' => $tableName]),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }

    private function constraintExists(string $tableName, string $constraintName): bool
    {
        $sql = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM information_schema.table_constraints
            WHERE constraint_schema = :schema AND TABLE_NAME = :table_name
                AND CONSTRAINT_NAME = :constraint_name
        ) AS is_existing
        SQL;

        return (bool) $this->connection->executeQuery(
            $sql,
            [
                'schema' => $this->connection->getDatabase(),
                'table_name' => $tableName,
                'constraint_name' => $constraintName,
            ]
        )->fetchOne();
    }

    private function getForeignKeyName(string $tableName, string $columnName): ?string
    {
        $sql = <<<SQL
        SELECT CONSTRAINT_NAME FROM information_schema.key_column_usage
        WHERE CONSTRAINT_SCHEMA = :schema AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name
            AND REFERENCED_TABLE_NAME = 'pim_catalog_product' AND REFERENCED_COLUMN_NAME = 'id'
        SQL;

        return $this->connection->executeQuery(
            $sql,
            [
                'schema' => $this->connection->getDatabase(),
                'table_name' => $tableName,
                'column_name' => $columnName,
            ]
        )->fetchOne();
    }

    private static function getInsertTriggerName(string $tableName): string
    {
        // Some tables are too long, so we shorten them (trigger names are limited to 64 characters)
        // Need to be the same function as in MigrateToUuidAddTriggers (we cannot use it because of coupling detector)
        $trigger_prefix = \strtr($tableName, [
            'data_quality_insights' => 'dqi',
            'teamwork_assistant' => 'twa',
        ]);

        return $trigger_prefix . '_uuid_insert';
    }

    private static function getUpdateTriggerName(string $tableName): string
    {
        // Some tables are too long, so we shorten them (trigger names are limited to 64 characters)
        // Need to be the same function as in MigrateToUuidAddTriggers (we cannot use it because of coupling detector)
        $trigger_prefix = \strtr($tableName, [
            'data_quality_insights' => 'dqi',
            'teamwork_assistant' => 'twa',
        ]);

        return $trigger_prefix . '_uuid_update';
    }
}
