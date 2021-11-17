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
}
