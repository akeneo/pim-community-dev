<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration will ad a column "naming_convention" to the asset family table.
 */
final class Version_4_0_20191223153556_add_asset_naming_convention_column extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $alterTable = <<<SQL
ALTER TABLE akeneo_asset_manager_asset_family
ADD COLUMN `naming_convention` json DEFAULT '[]'
SQL;

        $this->addSql($alterTable);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
