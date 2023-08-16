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

class Version_8_0_20230811151128_add_check_json_schema_to_workflow_and_task_translation_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230811151128_add_check_json_schema_to_workflow_and_task_translation';

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
        $this->dropCheckIfExists('CHK_workflow_translation_json', 'akeneo_workflow');
        $this->dropCheckIfExists('CHK_workflow_task_translation_json', 'akeneo_workflow_task');
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->checkExists('CHK_workflow_translation_json', 'akeneo_workflow'));
        Assert::assertTrue($this->checkExists('CHK_workflow_task_translation_json', 'akeneo_workflow_task'));
    }

    private function dropCheckIfExists(string $checkName, string $tableName): void
    {
        Assert::assertTrue($this->tableExists($tableName));
        if ($this->checkExists($checkName, $tableName)) {
            $this->connection->executeQuery(
                sprintf('ALTER TABLE %s DROP CONSTRAINT %s', $tableName, $checkName)
            );
        }

        Assert::assertEquals(false, $this->checkExists($checkName, $tableName));
    }

    private function checkExists(string $checkName, string $tableName): bool
    {
        $sql = <<<SQL
        SELECT CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS
        WHERE CONSTRAINT_NAME = :constraint_name;
        SQL;

        return $this->connection->executeQuery($sql, [
                'database' => $this->connection->getDatabase(),
                'table_name' => $tableName,
                'constraint_name' => $checkName,
            ])->rowCount() >= 1;
    }

    /**
     * @throws Exception
     */
    private function tableExists(string $tableName): bool
    {
        return $this->connection->executeQuery(
                'SHOW TABLES LIKE :table_name',
                [
                    'table_name' => $tableName,
                ]
            )->rowCount() >= 1;
    }
}
