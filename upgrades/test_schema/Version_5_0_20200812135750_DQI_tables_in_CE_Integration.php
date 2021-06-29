<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

class Version_5_0_20200812135750_DQI_tables_in_CE_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200812135750_DQI_tables_in_CE';

    public function test_it_creates_dqi_tables(): void
    {
        $this->get('database_connection')->executeQuery('DROP TABLE IF EXISTS pim_data_quality_insights_dashboard_rates_projection');
        $this->get('database_connection')->executeQuery('DROP TABLE IF EXISTS pim_data_quality_insights_product_axis_rates');
        $this->get('database_connection')->executeQuery('DROP TABLE IF EXISTS pim_data_quality_insights_product_criteria_evaluation');
        $this->get('database_connection')->executeQuery('DROP TABLE IF EXISTS pim_data_quality_insights_product_model_axis_rates');
        $this->get('database_connection')->executeQuery('DROP TABLE IF EXISTS pim_data_quality_insights_product_model_criteria_evaluation');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $shemaManager = $this->get('database_connection')->getSchemaManager();
        $this->assertTrue($shemaManager->tablesExist('pim_data_quality_insights_dashboard_rates_projection'));
        $this->assertTrue($shemaManager->tablesExist('pim_data_quality_insights_product_axis_rates'));
        $this->assertTrue($shemaManager->tablesExist('pim_data_quality_insights_product_criteria_evaluation'));
        $this->assertTrue($shemaManager->tablesExist('pim_data_quality_insights_product_model_axis_rates'));
        $this->assertTrue($shemaManager->tablesExist('pim_data_quality_insights_product_model_criteria_evaluation'));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
