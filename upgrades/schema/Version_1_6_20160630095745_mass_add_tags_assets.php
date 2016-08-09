<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add the background job in database to mass add tags on product assets
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class Version_1_6_20160630095745_mass_add_tags_assets extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(<<<SQL
            INSERT INTO akeneo_batch_job_instance
                (`code`, `label`, `job_name`, `status`, `connector`, `rawConfiguration`, `type`)
            VALUES
                ('add_tags_to_assets', 'Add tags to assets', 'add_tags_to_assets', 0, 'Akeneo Product Asset Connector', 'a:0:{}', 'mass_edit')
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
                        'add_tags_to_assets'
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
