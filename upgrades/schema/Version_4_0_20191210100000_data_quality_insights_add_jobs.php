<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create the job instance to evaluate products for Data Quality Insights
 */
class Version_4_0_20191210100000_data_quality_insights_add_jobs extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $jobEvaluateProductsCriteria = EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME;

        $this->addSql(<<<SQL
            INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES 
           (
                '$jobEvaluateProductsCriteria',
                '$jobEvaluateProductsCriteria',
                '$jobEvaluateProductsCriteria',
                0,
                'Data Quality Insights Connector', 
                'a:0:{}',
                'data_quality_insights'
            ),
           (
                'data_quality_insights_periodic_tasks',
                'data_quality_insights_periodic_tasks',
                'data_quality_insights_periodic_tasks',
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
