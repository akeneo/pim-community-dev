<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_3_2_X_20191204134851_franklin_insights_add_job_push_structure_and_products extends AbstractMigration
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
