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
            $indexNamesToDelete = \array_keys($tableProperties[MigrateToUuidStep::TEMPORARY_INDEXES_INDEX] ?? []);
            $indexNamesToDelete[] = self::TEMPORARY_INDEX_NAME;

            foreach ($indexNamesToDelete as $indexName) {
                if ($this->indexExists($tableName, $indexName)) {
                    $this->addSql(\strtr(
                        'ALTER TABLE {tableName} DROP INDEX {indexName};',
                        ['{tableName}' => $tableName, '{indexName}' => $indexName]
                    ));
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

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
