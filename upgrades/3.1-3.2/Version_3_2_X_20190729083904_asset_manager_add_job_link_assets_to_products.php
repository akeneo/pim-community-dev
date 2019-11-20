<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adds the job to link assets to products (for assets manager)
 */
class Version_3_2_X_20190729083904_asset_manager_add_job_link_assets_to_products extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(<<<SQL
        INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
        VALUES
	        ('asset_manager_link_assets_to_products', 'asset manager link assets to products', 'asset_manager_link_assets_to_products', 0, 'internal', 'a:0:{}', 'asset_manager_link_assets_to_products')
        ;
SQL
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
