<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

final class Version_6_0_20210527144217_dqi_recompute_products_scores_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    const MIGRATION_LABEL = '_6_0_20210527144217_dqi_recompute_products_scores';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItInitiateProductsScoresRecomputing(): void
    {
        $this->givenProductsScores();
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertProductsScoresAreEmpty();
        $this->assertRecomputeProductsScoresJobIsCreated();
    }

    private function givenProductsScores(): void
    {
        $insertProducts = <<<SQL
INSERT INTO pim_catalog_product (id, family_id, product_model_id, family_variant_id, is_enabled, identifier, raw_values, created, updated) VALUES
    (1, NULL, NULL, NULL, 1, 'product1', '{"sku": {"<all_channels>": {"<all_locales>": "product1"}}}', '2021-05-31 10:42:00', '2021-05-31 10:42:00'),
    (2, NULL, NULL, NULL, 1, 'product2', '{"sku": {"<all_channels>": {"<all_locales>": "product2"}}}', '2021-05-31 10:42:01', '2021-05-31 10:42:01')
SQL;

        $this->get('database_connection')->executeQuery($insertProducts);

        $insertScores = <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_id, evaluated_at, scores) VALUES 
    (1, '2021-05-31', '{}'),
    (1, '2021-05-22', '{}'),
    (2, '2021-05-31', '{}')
SQL;

        $this->get('database_connection')->executeQuery($insertScores);
    }

    private function assertProductsScoresAreEmpty(): void
    {
        $query = <<<SQL
SELECT COUNT(*) FROM pim_data_quality_insights_product_score;
SQL;

        $countScores = $this->get('database_connection')->executeQuery($query)->fetchColumn();

        $this->assertSame('0', $countScores, 'Products scores should be empty.');
    }

    private function assertRecomputeProductsScoresJobIsCreated(): void
    {
        $findJobInstance = <<<SQL
SELECT id FROM akeneo_batch_job_instance WHERE code = 'data_quality_insights_recompute_products_scores';
SQL;

        $jobInstanceId = $this->get('database_connection')->executeQuery($findJobInstance)->fetchColumn();
        $this->assertNotFalse($jobInstanceId, 'Job instance not found.');
    }
}
