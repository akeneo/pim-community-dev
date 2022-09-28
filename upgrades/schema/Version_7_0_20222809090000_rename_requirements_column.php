<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration adds the Syndication platform table
 */
final class Version_7_0_20222809090000_rename_requirements_column extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER TABLE `akeneo_syndication_family`
            RENAME COLUMN `data` TO `requirements`;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
