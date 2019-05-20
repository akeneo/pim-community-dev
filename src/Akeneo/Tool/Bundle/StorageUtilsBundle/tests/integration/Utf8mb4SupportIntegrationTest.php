<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\tests\integration;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

/**
 * Checks that Utf8mb4Support is properly taken into account by Doctrine ORM
 * and supported by the MySQL DB.
 */
class Utf8mb4SupportIntegrationTest extends TestCase
{
    const TEST_TABLE_NAME = "test_integration_storageutils_utf8mb4";

    /** @var AbstractSchemaManager */
    protected $schemaManager;

    /** @var Connection */
    protected $connection;

    public function setUp(): void
    {
        parent::setup();
        $this->connection = $this->get('doctrine.orm.entity_manager')->getConnection();
        $this->schemaManager = $this->connection->getSchemaManager();
    }

    public function testUtf8mb4Support() : void
    {
        if ($this->schemaManager->tablesExist([self::TEST_TABLE_NAME])) {
            $this->schemaManager->dropTable(self::TEST_TABLE_NAME);
        }

        $schema = $this->schemaManager->createSchema();

        $myTestTable = $schema->createTable(self::TEST_TABLE_NAME);
        $myTestTable->addColumn('id', 'integer');
        $myTestTable->addColumn('name', 'string');
        $myTestTable->setPrimaryKey(['id']);

        $myTestTableSql = array_filter(
            $schema->toSql($this->connection->getDatabasePlatform()),
            function ($sql) {
                return (strpos($sql, 'CREATE TABLE '.self::TEST_TABLE_NAME) === 0);
            }
        );
        $myTestTableSql = reset($myTestTableSql);

        $this->connection->exec($myTestTableSql);

        $insertCount = $this->connection->insert(
            self::TEST_TABLE_NAME,
            [
                'id' =>1,
                'name' => '𝌆'
            ]
        );

        $this->assertEquals(1, $insertCount);

        $resultFromDb = $this->connection->fetchColumn(
            "SELECT name FROM ".self::TEST_TABLE_NAME." WHERE name = ?",
            ["𝌆"]
        );

        $this->assertEquals("𝌆", $resultFromDb);
    }

    public function tearDown() : void
    {
        if ($this->schemaManager->tablesExist([self::TEST_TABLE_NAME])) {
            $this->schemaManager->dropTable(self::TEST_TABLE_NAME);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return null;
    }
}
