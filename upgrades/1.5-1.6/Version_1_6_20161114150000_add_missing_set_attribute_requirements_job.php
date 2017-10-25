<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add the missing set_attribute_requirements job
 *
 * @author   Thomas Neumann <thomas.neumann@aoe.com
 * @license  none none
 */
class Version_1_6_20161114150000_add_missing_set_attribute_requirements_job extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->write('(re-)create set_attribute_requirements job');
        $this->addSql("DELETE FROM akeneo_batch_job_instance WHERE code = 'set_attribute_requirements';");
        $this->addSql(<<< SQL
            INSERT INTO `akeneo_batch_job_instance`
              (`code`, `label`, `job_name`, `status`, `connector`, `type`, `raw_parameters`)
            VALUES
              (
                'set_attribute_requirements',
                'Set Attribute Requirements',
                'set_attribute_requirements',
                '0',
                'Akeneo Mass Edit Connector',
                'mass_edit',
                'a:2:{s:7:\"filters\";a:0:{}s:7:\"actions\";a:0:{}}'
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
