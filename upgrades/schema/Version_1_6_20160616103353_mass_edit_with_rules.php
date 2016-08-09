<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add the new mass edit jobs that apply rules in "akeneo_batch_job_instance"
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class Version_1_6_20160616103353_mass_edit_with_rules extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(<<<SQL
            INSERT INTO akeneo_batch_job_instance
                (`code`, `label`, `alias`, `status`, `connector`, `rawConfiguration`, `type`)
            VALUES
                ('remove_product_value_with_permission', 'Mass remove products values with permission check', 'remove_product_value_with_permission', 0,'Akeneo Mass Edit Connector', 'a:0:{}', 'mass_edit'),
                ('remove_product_value_with_permission_and_rules', 'Mass remove products values with permission check & rules application', 'remove_product_value_with_permission_and_rules', 0,'Akeneo Mass Edit Connector', 'a:0:{}', 'mass_edit')
            ;
SQL
        );

        $this->addSql(<<<SQL
            INSERT INTO pimee_security_job_profile_access
                (`job_profile_id`, `user_group_id`, `execute_job_profile`, `edit_job_profile`)
                    SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
                    FROM akeneo_batch_job_instance as j
                    JOIN oro_access_group AS g ON g.name = "All"
                    WHERE j.code IN (
                        'remove_product_value_with_permission',
                        'remove_product_value_with_permission_and_rules'
                    )
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
