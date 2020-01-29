<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version_4_0_20200128163617_franklin_insights_add_job_push_structure_and_products extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $jobName = JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS;

        $this->addSql(<<<SQL
        INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
        VALUES (
            '$jobName',
            '$jobName', 
            '$jobName', 
            0, 
            'Franklin Insights Connector', 
            'a:0:{}', 
            'franklin_insights'
        );
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
