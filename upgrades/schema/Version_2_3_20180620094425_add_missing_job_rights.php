<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adds missing job rights for jobs added in CE
 */
class Version_2_3_20180620094425_add_missing_job_rights extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql = <<<SQL
            INSERT INTO pimee_security_job_profile_access (`job_profile_id`, `user_group_id`, `execute_job_profile`, `edit_job_profile`)
                    SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
                    FROM akeneo_batch_job_instance as j
                    JOIN oro_access_group AS g ON g.name = "All"
                    WHERE j.code = 'change_parent_product' OR j.code = 'add_to_group'
            ;
SQL;

        $this->addSql($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
