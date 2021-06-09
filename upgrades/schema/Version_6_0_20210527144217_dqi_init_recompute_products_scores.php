<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20210527144217_dqi_init_recompute_products_scores extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->truncateProductsScoresTable();

        $this->createRecomputeJobInstance();
    }

    private function truncateProductsScoresTable(): void
    {
        $sql = <<<SQL
TRUNCATE TABLE pim_data_quality_insights_product_score
SQL;
        $this->addSql($sql);
    }

    private function createRecomputeJobInstance(): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
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
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
