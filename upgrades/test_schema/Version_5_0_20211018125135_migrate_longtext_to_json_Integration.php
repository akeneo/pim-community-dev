<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20211018125135_migrate_longtext_to_json_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;

    /** @test */
    public function longTextColumnsAreMigratedInJson(): void
    {
        $this->connection->executeQuery('ALTER TABLE pimee_workflow_product_draft MODIFY COLUMN changes longtext NOT NULL');
        $this->connection->executeQuery('ALTER TABLE akeneo_rule_engine_rule_definition MODIFY COLUMN content longtext');
        $this->connection->executeQuery(<<<SQL
        INSERT INTO akeneo_rule_engine_rule_definition (code, type, content) VALUES
        ('my_rule', 'type', '{"a": "b"}');
        SQL);
        Assert::assertSame('longtext', $this->getColumnType('pimee_workflow_product_draft', 'changes'));
        Assert::assertSame('NO', $this->getColumnNullable('pimee_workflow_product_draft', 'changes'));
        Assert::assertSame('longtext', $this->getColumnType('akeneo_rule_engine_rule_definition', 'content'));
        Assert::assertSame('YES', $this->getColumnNullable('akeneo_rule_engine_rule_definition', 'content'));

        $this->reExecuteMigration($this->getMigrationLabel());

        Assert::assertSame('json', $this->getColumnType('pimee_workflow_product_draft', 'changes'));
        Assert::assertSame('NO', $this->getColumnNullable('pimee_workflow_product_draft', 'changes'));
        Assert::assertSame('json', $this->getColumnType('akeneo_rule_engine_rule_definition', 'content'));
        Assert::assertSame('YES', $this->getColumnNullable('akeneo_rule_engine_rule_definition', 'content'));

        $content = $this->connection->executeQuery('SELECT content FROM akeneo_rule_engine_rule_definition WHERE code = \'my_rule\'')->fetch(\PDO::FETCH_COLUMN);
        Assert::assertNotFalse($content);
        Assert::assertSame(['a' => 'b'], \json_decode($content, true));
    }

    private function getColumnType(string $tableName, string $columnName): string
    {
        $sql = <<<SQL
        SELECT DATA_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = :db_name AND table_name = :table_name AND column_name = :column_name
        SQL;

        $statement = $this->connection->executeQuery($sql, [
            'db_name' => $this->connection->getParams()['dbname'],
            'table_name' => $tableName,
            'column_name' => $columnName,
        ]);

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    private function getColumnNullable(string $tableName, string $columnName): string
    {
        $sql = <<<SQL
        SELECT IS_NULLABLE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = :db_name AND table_name = :table_name AND column_name = :column_name
        SQL;

        $statement = $this->connection->executeQuery($sql, [
            'db_name' => $this->connection->getParams()['dbname'],
            'table_name' => $tableName,
            'column_name' => $columnName,
        ]);

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }

    public function setUp() : void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
