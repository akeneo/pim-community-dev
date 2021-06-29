<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_5_0_20201118133700_migrate_product_axis_rate_to_unique_score_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20201118133700_migrate_product_axis_rate_to_unique_score';

    public function test_nothing_to_migrate_if_no_evaluations(): void
    {
        $this->ensureProductAxisRatesTableIsCreatedAndEmpty();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $query = <<<SQL
    SELECT count(scores) as total FROM pim_data_quality_insights_product_score
    SQL;

        $result = $this->getConnection()->executeQuery($query)->fetchColumn();

        $this->assertEquals($result, 0);
    }

    public function test_migrate_from_two_axis_to_one_unique_score(): void
    {
        $this->ensureProductAxisRatesTableIsCreatedAndEmpty();

        $this->getConnection()->executeQuery(<<<SQL
INSERT IGNORE INTO pim_catalog_product (id, product_model_id, is_enabled, identifier, raw_values, created, updated)
VALUES
    (5000, null, 1, 'product1', '{"name": {"<all_channels>": {"<all_locales>": ""}}}', NOW(), NOW()),
    (5001, null, 1, 'product2', '{"name": {"<all_channels>": {"<all_locales>": []}}}', NOW(), NOW()),
    (5002, null, 1, 'product3', '{"name": {"<all_channels>": {"<all_locales>": [""]}}}', NOW(), NOW()),
    (5003, null, 1, 'product4', '{"name": {"<all_channels>": {"<all_locales>": ""}}}', NOW(), NOW())
SQL);

        //CE kind of products with enrichment axis only
        $this->getConnection()->executeQuery(<<<SQL
INSERT INTO pim_data_quality_insights_product_axis_rates (product_id, axis_code, evaluated_at, rates) VALUES (5000, 'enrichment', '2020-11-18', '{"ecommerce": {"en_US": {"rank": 5, "value": 34}}}');
INSERT INTO pim_data_quality_insights_product_axis_rates (product_id, axis_code, evaluated_at, rates) VALUES (5001, 'enrichment', '2020-11-18', '[]');
INSERT INTO pim_data_quality_insights_product_axis_rates (product_id, axis_code, evaluated_at, rates) VALUES (5002, 'enrichment', '2020-11-18', '{"ecommerce": {"en_US": {"rank": 5, "value": 30}}}');
SQL);

        //EE kind of products with enrichment and consistency axis
        $this->getConnection()->executeQuery(<<<SQL
INSERT INTO pim_data_quality_insights_product_axis_rates (product_id, axis_code, evaluated_at, rates) VALUES (5003, 'enrichment', '2020-11-18', '{"ecommerce": {"en_US": {"rank": 5, "value": 10}}}');
INSERT INTO pim_data_quality_insights_product_axis_rates (product_id, axis_code, evaluated_at, rates) VALUES (5003, 'consistency', '2020-11-18', '{"ecommerce": {"en_US": {"rank": 5, "value": 20}}}');
SQL);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFromAxisRatesToUniqueScoreIsComputed(5000, '{"ecommerce": {"en_US": {"rank": 5, "value": 34}}}');
        $this->assertFromAxisRatesToUniqueScoreIsComputed(5001, '[]');
        $this->assertFromAxisRatesToUniqueScoreIsComputed(5002, '{"ecommerce": {"en_US": {"rank": 5, "value": 30}}}');
        $this->assertFromAxisRatesToUniqueScoreIsComputed(5003, '{"ecommerce": {"en_US": {"rank": 5, "value": 15}}}');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assertFromAxisRatesToUniqueScoreIsComputed(int $productId, string $scores): void
    {
        $query = <<<SQL
    SELECT scores FROM pim_data_quality_insights_product_score
    WHERE product_id = :productId
    SQL;

        $result = $this->getConnection()->executeQuery(
            $query,
            [
                'productId' => $productId,
            ]
        )->fetchColumn();

        $this->assertEquals($result, $scores);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function ensureProductAxisRatesTableIsCreatedAndEmpty(): void
    {
        $this->getConnection()->executeQuery(<<<SQL
CREATE TABLE IF NOT EXISTS pim_data_quality_insights_product_axis_rates (
    product_id INT NOT NULL,
    axis_code VARCHAR(40) NOT NULL,
    evaluated_at DATE NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (product_id, axis_code, evaluated_at),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE pim_data_quality_insights_product_axis_rates;
TRUNCATE TABLE pim_data_quality_insights_product_score;
SQL);
    }

}
