<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add TWA jobs
 */
class Version_1_7_20171219666666_import_missing_job_right extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql = <<<SQL
REPLACE INTO akeneo_batch_job_instance (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`) VALUES
('project_calculation', 'Project calculation', 'project_calculation', 0, 'teamwork assistant', 'a:0:{}', 'project_calculation'),
('refresh_project_completeness_calculation', 'Refresh project completeness', 'refresh_project_completeness_calculation', 0, 'teamwork assistant', 'a:0:{}', 'refresh_project_completeness_calculation');

INSERT INTO `pimee_security_job_profile_access` (`job_profile_id`,`user_group_id`,`execute_job_profile`,`edit_job_profile`)
SELECT
	(SELECT id FROM akeneo_batch_job_instance WHERE code = 'project_calculation') as job_profile_id,
    id as user_group_id,
    1,
    1
FROM `oro_access_group`;

INSERT INTO `pimee_security_job_profile_access` (`job_profile_id`,`user_group_id`,`execute_job_profile`,`edit_job_profile`)
SELECT
	(SELECT id FROM akeneo_batch_job_instance WHERE code = 'refresh_project_completeness_calculation') as job_profile_id,
    id as user_group_id,
    1,
    1
FROM `oro_access_group`;
SQL;

        $this->connection->exec($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
