<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20210531152335_dqi_launch_recompute_products_scores_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    const MIGRATION_LABEL = '_6_0_20210531152335_dqi_launch_recompute_products_scores';

    public function testRecomputeScoresJobShouldBeInitialized(): void
    {
        // Given the `data_quality_insights_recompute_products_scores` job instance is created
        $this->get('database_connection')->executeQuery(<<<SQL
INSERT IGNORE INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
    (
        'data_quality_insights_recompute_products_scores',
        'data_quality_insights_recompute_products_scores',
        'data_quality_insights_recompute_products_scores',
        0,
        'Data Quality Insights Connector',
        'a:0:{}',
        'data_quality_insights'
    );
SQL);
        // Execute the migration
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        // assert the job execution is in akeneo_batch_job_execution table
        $statement = $this->get('database_connection')->executeQuery(<<<SQL
            SELECT COUNT(abje.id) FROM akeneo_batch_job_execution abje
            INNER JOIN akeneo_batch_job_instance abji ON abje.job_instance_id = abji.id
            WHERE abji.code = 'data_quality_insights_recompute_products_scores';
        SQL);
        $jobExecutionId = (int) $statement->fetchOne();
        $this->assertNotNull($jobExecutionId,  'The `data_quality_insights_recompute_products_scores` job execution should be initialized');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
