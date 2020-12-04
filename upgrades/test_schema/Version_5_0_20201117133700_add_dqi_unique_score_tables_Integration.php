<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_5_0_20201117133700_add_dqi_unique_score_tables_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20201117133700_add_dqi_unique_score_tables';

    public function test_migrate_from_two_axis_to_one_unique_score(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
DROP TABLE IF EXISTS pim_data_quality_insights_product_score;
DROP TABLE IF EXISTS pim_data_quality_insights_dashboard_scores_projection;
SQL);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $this->assertTrue($schemaManager->tablesExist('pim_data_quality_insights_product_score'));
        $this->assertTrue($schemaManager->tablesExist('pim_data_quality_insights_dashboard_scores_projection'));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
