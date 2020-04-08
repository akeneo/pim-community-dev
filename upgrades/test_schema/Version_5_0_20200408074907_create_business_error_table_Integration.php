<?php
declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

class Version_5_0_20200408074907_create_business_error_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    /** @var Connection */
    private $dbalConnection;

    /** @var AbstractSchemaManager */
    private $schemaManager;

    private const MIGRATION_LABEL = '_5_0_20200408074907_create_business_error_table';

    public function test_it_creates_the_business_error_table(): void
    {
        $this->dbalConnection->executeQuery('DROP TABLE IF EXISTS akeneo_connectivity_connection_audit_business_error');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->schemaManager->tablesExist('akeneo_connectivity_connection_audit_business_error'));
        $this->assertBusinessErrorTableColumns();
    }

    public function test_it_does_not_break_if_the_table_already_exists(): void
    {
        $this->dbalConnection->executeQuery('CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_audit_business_error (connection_code VARCHAR(100))');

        $this->reExecuteMigration(self::MIGRATION_LABEL);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->schemaManager  = $this->dbalConnection->getSchemaManager();
    }

    private function assertBusinessErrorTableColumns(): void
    {
        $expectedColumnsAndTypes = [
            'connection_code' => 'varchar(100)',
            'error_datetime' => 'datetime',
            'content' => 'json',
        ];

        $columns = $this->dbalConnection
            ->executeQuery('SHOW COLUMNS FROM akeneo_connectivity_connection_audit_business_error')
            ->fetchAll();

        Assert::assertCount(3, $columns);
        foreach ($columns as $column) {
            Assert::assertArrayHasKey($column['Field'], $expectedColumnsAndTypes);
            Assert::assertEquals($expectedColumnsAndTypes[$column['Field']], $column['Type']);
            Assert::assertEquals('NO', $column['Null']);
        }
    }
}
