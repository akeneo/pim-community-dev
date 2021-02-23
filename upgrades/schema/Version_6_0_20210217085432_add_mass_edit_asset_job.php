<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20210217085432_add_mass_edit_asset_job extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`) 
            VALUES ('asset_manager_mass_edit_assets', 'Mass edit assets', 'asset_manager_mass_edit_assets', 0, 'internal', 'a:0:{}', 'asset_manager_mass_edit_assets');
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
