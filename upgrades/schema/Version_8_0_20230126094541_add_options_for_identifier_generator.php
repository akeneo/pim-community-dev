<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration replaces delimiter by options
 */
final class Version_8_0_20230126094541_add_options_for_identifier_generator extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'This migration replaces delimiter by options';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('pim_catalog_identifier_generator', 'delimiter')) {
            return;
        }

        $sql = <<<SQL
ALTER TABLE pim_catalog_identifier_generator ADD COLUMN options JSON NOT NULL AFTER delimiter;
UPDATE pim_catalog_identifier_generator SET options=JSON_OBJECT('delimiter', delimiter, 'text_transformation', 'no');
ALTER TABLE pim_catalog_identifier_generator DROP COLUMN delimiter;
SQL;

        $this->addSql($sql);    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr(
                <<<SQL
                    SHOW COLUMNS FROM {table_name} LIKE :columnName
                SQL,
                ['{table_name}' => $tableName]
            ),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }
}
