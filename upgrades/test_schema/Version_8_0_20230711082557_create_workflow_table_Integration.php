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
use PHPUnit\Framework\Assert;

class Version_8_0_20230711082557_create_workflow_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230711082557_create_workflow_table';
    private const TABLE_NAME = 'akeneo_workflow';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_creates_the_akeneo_workflow_table_if_not_present(): void
    {
        Assert::assertTrue($this->tableExists());
        $this->connection->executeStatement('DROP TABLE IF EXISTS akeneo_workflow');
        Assert::assertFalse($this->tableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists());
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_does_not_fail_if_the_akeneo_workflow_table_is_already_created(): void
    {
        Assert::assertTrue($this->tableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists());
    }

    /**
     * @throws Exception
     */
    private function tableExists(): bool
    {
        return $this->connection->executeQuery(
                'SHOW TABLES LIKE :tableName',
                [
                    'tableName' => self::TABLE_NAME,
                ]
            )->rowCount() >= 1;
    }
}
