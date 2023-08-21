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
use Doctrine\DBAL\Exception;

final class Version_8_0_20230817091922_add_created_column_to_akeneo_workflow_entity_task_table_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230817091922_add_created_column_to_akeneo_workflow_entity_task_table';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_adds_created_column(): void
    {
        if ($this->hasCreatedColumn()) {
            $this->dropCreatedColumn();
        }

        $this->reExecuteMigration(self::MIGRATION_NAME);
        $this->assertTrue($this->hasCreatedColumn());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @throws Exception
     */
    private function hasCreatedColumn(): bool
    {
        return $this->connection->executeQuery(
            <<<SQL
                SHOW COLUMNS FROM akeneo_workflow_entity_task LIKE 'created';
            SQL,
        )->rowCount() >= 1;
    }

    /**
     * @throws Exception
     */
    private function dropCreatedColumn(): void
    {
        $sql = <<<SQL
            ALTER TABLE akeneo_workflow_entity_task DROP COLUMN created;
        SQL;

        $this->connection->executeQuery($sql);
    }
}
