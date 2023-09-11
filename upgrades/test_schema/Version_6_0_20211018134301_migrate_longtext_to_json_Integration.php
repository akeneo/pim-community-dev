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

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_6_0_20211018134301_migrate_longtext_to_json_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;

    /** @test */
    public function longTextColumnsAreMigratedInJson(): void
    {
        $this->connection->executeQuery('ALTER TABLE oro_user MODIFY COLUMN properties longtext NOT NULL');
        $this->connection->executeQuery(<<<SQL
        INSERT INTO oro_user (ui_locale_id, username, email, createdAt, updatedAt, salt, password, timezone, properties)
        SELECT
               id, 'test', 'test@example.com', now(), now(), 'salt', 'password', 'timezone', '{"a":"b"}'
        FROM pim_catalog_locale locale
        WHERE locale.code = 'en_US'
        SQL);
        Assert::assertSame('longtext', $this->getColumnType('oro_user', 'properties'));
        Assert::assertSame('NO', $this->getColumnNullable('oro_user', 'properties'));

        $this->reExecuteMigration($this->getMigrationLabel());

        Assert::assertSame('json', $this->getColumnType('oro_user', 'properties'));
        Assert::assertSame('NO', $this->getColumnNullable('oro_user', 'properties'));
        $properties = $this->connection->executeQuery('SELECT properties FROM oro_user WHERE username = \'test\'')->fetchOne();
        Assert::assertNotFalse($properties);
        Assert::assertSame(['a' => 'b'], \json_decode($properties, true));
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

        return $statement->fetchOne();
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

        return $statement->fetchOne();
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
