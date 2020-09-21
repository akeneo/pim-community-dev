<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

final class Version_5_0_20200921153252_dqi_create_table_attribute_group_activation_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200921153252_dqi_create_table_attribute_group_activation';

    public function test_it_creates_the_attribute_group_activation_table(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
DROP TABLE IF EXISTS pim_data_quality_insights_attribute_group_activation
SQL
        );

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $this->assertTrue($schemaManager->tablesExist('pim_data_quality_insights_attribute_group_activation'));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
