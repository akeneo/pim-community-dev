<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;


class Version_7_0_20220214101647_add_dqi_product_model_score_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220214101647_add_dqi_product_model_score_table';

    public function test_migrate_dqi_product_model_score_table(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
DROP TABLE IF EXISTS pim_data_quality_insights_product_model_score;
SQL);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $this->assertTrue($schemaManager->tablesExist('pim_data_quality_insights_product_model_score'));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
