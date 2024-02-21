<?php

namespace Akeneo\Tool\Bundle\FileStorageBundle\Command;

use Akeneo\Platform\Installer\Infrastructure\Command\ZddMigration;
use Doctrine\DBAL\Connection;

class V20230324153137AddIndexOnFileInfoZddMigration implements ZddMigration
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function migrate(): void
    {
        if ($this->indexExists()) {
            return;
        }

        $sql = <<<SQL
CREATE INDEX original_filename_hash_storage_idx ON akeneo_file_storage_file_info (original_filename, hash, storage) LOCK = NONE;
SQL;

        $this->connection->executeQuery($sql);
    }

    /**
     * TODO: Call this method in a classic doctrine migration for Saas.
     * It should be done when we're sure this migration has been executed in a Zdd way on production.
     */
    public function migrateNotZdd(): void
    {
        $this->migrate();
    }

    public function getName(): string
    {
        return 'V20230324153137AddIndexOnFileInfoZddMigration';
    }

    private function indexExists(): bool
    {
        $sql = <<<SQL
SHOW INDEX FROM akeneo_file_storage_file_info WHERE Key_name = 'original_filename_hash_storage_idx';
SQL;

        return 0 < $this->connection->executeQuery($sql)->rowCount();
    }
}
