<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20210916402528_make_naming_convention_not_nullable extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("UPDATE akeneo_asset_manager_asset_family SET naming_convention='[]' WHERE naming_convention IS NULL");

        $this->addSql("ALTER TABLE akeneo_asset_manager_asset_family MODIFY `naming_convention` json NOT NULL");
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
