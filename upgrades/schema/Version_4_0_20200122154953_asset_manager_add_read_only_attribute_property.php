<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_4_0_20200122154953_asset_manager_add_read_only_attribute_property extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(
            'ALTER TABLE akeneo_asset_manager_attribute ADD COLUMN `is_read_only` boolean NOT NULL DEFAULT false;'
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
