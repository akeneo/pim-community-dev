<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create the job instance to evaluate products for Data Quality Insights
 */
class Version_4_0_20191210100000_data_quality_insights_add_evaluation_job extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $jobName = EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME;
        $this->addSql(<<<SQL
            INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES (
                '$jobName',
                '$jobName',
                '$jobName',
                0,
                'Data Quality Insights Connector', 
                'a:0:{}',
                'data_quality_insights'
            );
SQL
        );
    }

    public function down(Schema $schema)
    {
    }
}
