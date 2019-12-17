<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds the job to compute transformations (for assets manager)
 */
final class Version_4_0_201911219142157_asset_manager_add_job_compute_transformations extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(<<<SQL
        INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
        VALUES
	        ('asset_manager_compute_transformations', 'asset manager compute transformations', 'asset_manager_compute_transformations', 0, 'internal', 'a:0:{}', 'asset_manager_compute_transformations')
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
