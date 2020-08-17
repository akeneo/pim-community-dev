<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\PimEnterpriseEvaluateProductsCriteriaTasklet;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create the job instance to evaluate products for Data Quality Insights
 */
class Version_4_0_20191210100000_data_quality_insights_add_evaluation_job extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $jobEvaluateProductsCriteria = 'data_quality_insights_evaluate_products_criteria';

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
        $this->throwIrreversibleMigrationException();
    }
}
