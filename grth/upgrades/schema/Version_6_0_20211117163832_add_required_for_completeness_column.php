<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211117163832_add_required_for_completeness_column extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the "is_required_for_completeness" column to table attributes columns';
    }

    public function up(Schema $schema): void
    {
        if ($this->columnExists()) {
            return;
        }

        $sql = <<<SQL
        ALTER TABLE pim_catalog_table_column
        ADD COLUMN is_required_for_completeness tinyint(1) NOT NULL
        SQL;

        $this->addSql($sql);

        $sql = <<<SQL
        UPDATE pim_catalog_table_column
        SET is_required_for_completeness = 1
        WHERE column_order = 0;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function columnExists(): bool
    {
        $checkColumnExistsSql = <<<SQL
        SELECT *
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=:db_name
          AND TABLE_NAME='pim_catalog_table_column'
          AND COLUMN_NAME='is_required_for_completeness';
        SQL;

        $statement = $this->connection->executeQuery($checkColumnExistsSql, [
            'db_name' => $this->connection->getParams()['dbname'],
        ]);

        return false !== $statement->fetchOne();
    }
}
