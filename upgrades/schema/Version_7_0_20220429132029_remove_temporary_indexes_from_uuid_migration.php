<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_7_0_20220429132029_remove_temporary_indexes_from_uuid_migration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove temporary indexes and constraints created during the migration from id to uuid';
    }

    public function up(Schema $schema): void
    {
        foreach (['pim_versioning_version', 'pim_comment_comment'] as $tableName) {
            if ($this->indexExists($tableName, 'migrate_to_uuid_temp_index_to_delete')) {
                $this->connection->executeQuery(
                    <<<SQL
                    ALTER TABLE {tableName}
                    DROP INDEX migrate_to_uuid_temp_index_to_delete;
                    SQL,
                    ['{tableName}' => $tableName]
                );
                echo sprintf("Temporary index dropped on table %s", $tableName);
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

        return (bool)$this->connection->executeQuery(
            $sql,
            [
                'schema' => $this->connection->getDatabase(),
                'table_name' => $tableName,
                'index_name' => $indexName,
            ]
        )->fetchOne();
    }
}
