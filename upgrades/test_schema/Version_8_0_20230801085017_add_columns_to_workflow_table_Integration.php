<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_8_0_20230801085017_add_columns_to_workflow_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_NAME = '_8_0_20230801085017_add_columns_to_workflow_table';
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_the_workflow_table_columns(): void
    {
        $this->reExecuteMigration(self::MIGRATION_NAME);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('akeneo_workflow');

        $this->assertArrayHasKey('translation', $tableColumns, 'The column `translation` should have been added to table `akeneo_workflow`');
        $this->assertArrayHasKey('enabled', $tableColumns, 'The column `enabled` should have been added to table `akeneo_workflow`');
        $this->assertArrayHasKey('created', $tableColumns, 'The column `created` should have been added to table `akeneo_workflow`');
        $this->assertArrayHasKey('updated', $tableColumns, 'The column `updated` should have been added to table `akeneo_workflow`');
        $this->assertArrayHasKey('deleted', $tableColumns, 'The column `deleted` should have been added to table `akeneo_workflow`');
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
