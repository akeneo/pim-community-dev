<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration will ad a column "transformations" to the asset family table.
 */
final class Version_4_0_20191119142157_add_asset_transformations_column extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $alterTable = <<<SQL
ALTER TABLE akeneo_asset_manager_asset_family
ADD COLUMN `transformations` json DEFAULT NULL
SQL;

        $this->addSql($alterTable);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
