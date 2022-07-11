<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_7_0_20220603081946_add_columns_in_dqi_dictionary_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220603081946_add_columns_in_dqi_dictionary_table';

    public function test_it_adds_the_datetime_column(): void
    {
        $query = <<<SQL
ALTER TABLE pimee_data_quality_insights_text_checker_dictionary 
DROP COLUMN enabled,
DROP COLUMN updated_at;
SQL;

        $this->get('database_connection')->executeQuery($query);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('pimee_data_quality_insights_text_checker_dictionary');

        $this->assertArrayHasKey('enabled', $tableColumns, 'The column `enabled` should have been added to table `pimee_data_quality_insights_text_checker_dictionary`');
        $this->assertArrayHasKey('updated_at', $tableColumns, 'The column `updated_at` should have been added to table `pimee_data_quality_insights_text_checker_dictionary`');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
