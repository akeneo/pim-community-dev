<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220415090329_add_uuid_column_for_comment extends AbstractMigration
{
    private const TABLE_NAME = 'pim_comment_comment';
    private const COLUMN_NAME = 'resource_uuid';

    public function getDescription(): string
    {
        return 'Add uuid column for pim_comment_comment table';
    }

    public function up(Schema $schema): void
    {
        if ($this->columnExists(self::TABLE_NAME, self::COLUMN_NAME)) {
            $this->disableMigrationWarning();
            return;
        }

        $addUuidColumnQuery = \strtr(
            'ALTER TABLE `{table_name}` ADD `{uuid_column_name}` BINARY(16) DEFAULT NULL COMMENT "(DC2Type:uuid_binary)";',
            [
                '{table_name}' => self::TABLE_NAME,
                '{uuid_column_name}' => self::COLUMN_NAME,
            ]
        );
        $this->addSql($addUuidColumnQuery);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr(
                'SHOW COLUMNS FROM `{table_name}` LIKE :columnName',
                ['{table_name}' => $tableName]
            ),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
