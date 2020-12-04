<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_5_0_20201118133701_migrate_dashboard_rates_projection_to_unique_score_projection_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20201118133701_migrate_dashboard_rates_projection_to_unique_score_projection';

    public function test_nothing_to_migrate_if_no_consolidations(): void
    {
        $this->ensureDashboardRatesTableIsCreatedAndEmpty();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $query = <<<SQL
    SELECT count(scores) as total FROM pim_data_quality_insights_dashboard_scores_projection
    SQL;

        $result = $this->getConnection()->executeQuery($query)->fetchColumn();

        $this->assertEquals($result, 0);
    }

    public function test_migrate_from_two_axis_to_one_unique_score(): void
    {
        $this->ensureDashboardRatesTableIsCreatedAndEmpty();

        //CE kind of consolidation with enrichment axis only
        $this->getConnection()->executeQuery(<<<SQL
INSERT INTO pim_data_quality_insights_dashboard_rates_projection (type, code, rates) VALUES ('catalog', 'catalog', '{"daily": {"2020-11-18": {"enrichment": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 10, "rank_3": 15, "rank_4": 20, "rank_5": 1337}}}}}, "average_ranks": {"enrichment": {"ecommerce": {"en_US": "rank_5"}}}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
INSERT INTO pim_data_quality_insights_dashboard_rates_projection (type, code, rates) VALUES ('category', 'categoryA', '{"weekly": {"2020-11-18": {"enrichment": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 10, "rank_3": 15, "rank_4": 20, "rank_5": 1337}}}}}, "average_ranks": {"enrichment": {"ecommerce": {"en_US": "rank_5"}}}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
INSERT INTO pim_data_quality_insights_dashboard_rates_projection (type, code, rates) VALUES ('category', 'categoryB', '{"monthly": {"2020-11-30": []}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
INSERT INTO pim_data_quality_insights_dashboard_rates_projection (type, code, rates) VALUES ('family', 'familyA', '{"yearly": {"2020-11-18": {"enrichment": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 10, "rank_3": 15, "rank_4": 20, "rank_5": 1337}}}}}, "average_ranks": {"enrichment": {"ecommerce": {"en_US": "rank_5"}}}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');

SQL);

        //EE kind of consolidation with enrichment and consistency axis
        $this->getConnection()->executeQuery(<<<SQL
INSERT INTO pim_data_quality_insights_dashboard_rates_projection (type, code, rates) VALUES ('family', 'familyB', '{"yearly": {"2020-11-18": {"enrichment": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 10, "rank_3": 15, "rank_4": 20, "rank_5": 1337}}}, "consistency": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 20, "rank_3": 15, "rank_4": 10, "rank_5": 1337}}}}}, "average_ranks": {"enrichment": {"ecommerce": {"en_US": "rank_5"}}}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
SQL);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFromAxisRatesToUniqueScoreIsComputed('catalog', 'catalog', '{"daily": {"2020-11-18": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 10, "rank_3": 15, "rank_4": 20, "rank_5": 1337}}}}, "average_ranks": {"ecommerce": {"en_US": "rank_5"}}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
        $this->assertFromAxisRatesToUniqueScoreIsComputed('category', 'categoryA', '{"weekly": {"2020-11-18": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 10, "rank_3": 15, "rank_4": 20, "rank_5": 1337}}}}, "average_ranks": {"ecommerce": {"en_US": "rank_5"}}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
        $this->assertFromAxisRatesToUniqueScoreIsComputed('category', 'categoryB', '{"average_ranks": [], "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
        $this->assertFromAxisRatesToUniqueScoreIsComputed('family', 'familyA', '{"yearly": {"2020-11-18": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 10, "rank_3": 15, "rank_4": 20, "rank_5": 1337}}}}, "average_ranks": {"ecommerce": {"en_US": "rank_5"}}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
        $this->assertFromAxisRatesToUniqueScoreIsComputed('family', 'familyB', '{"yearly": {"2020-11-18": {"ecommerce": {"en_US": {"rank_1": 5, "rank_2": 15, "rank_3": 15, "rank_4": 15, "rank_5": 1337}}}}, "average_ranks": {"ecommerce": {"en_US": "rank_5"}}, "average_ranks_consolidated_at": "2020-11-18 10:57:22"}');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assertFromAxisRatesToUniqueScoreIsComputed(string $type, string $code, string $scores): void
    {
        $query = <<<SQL
    SELECT scores FROM pim_data_quality_insights_dashboard_scores_projection
    WHERE type = :type 
    AND code = :code
    SQL;

        $result = $this->getConnection()->executeQuery(
            $query,
            [
                'type' => $type,
                'code' => $code,
            ]
        )->fetchColumn();

        $this->assertEquals($result, $scores);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function ensureDashboardRatesTableIsCreatedAndEmpty(): void
    {
        $this->getConnection()->executeQuery(<<<SQL
CREATE TABLE IF NOT EXISTS pim_data_quality_insights_dashboard_rates_projection (
    type VARCHAR(15) NOT NULL,
    code VARCHAR(100) NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (type, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE pim_data_quality_insights_dashboard_rates_projection;
TRUNCATE TABLE pim_data_quality_insights_dashboard_scores_projection;
SQL);
    }
}
