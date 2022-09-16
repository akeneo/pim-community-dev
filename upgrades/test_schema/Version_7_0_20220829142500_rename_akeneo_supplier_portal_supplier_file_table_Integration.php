<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

/** @group migration-supplier-portal */
final class Version_7_0_20220829142500_rename_akeneo_supplier_portal_supplier_file_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220829142500_rename_akeneo_supplier_portal_supplier_file_table';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_renames_the_akeneo_supplier_portal_supplier_file_table_to_akeneo_supplier_portal_supplier_product_file(): void
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        static::assertFalse($this->tableExists('akeneo_supplier_portal_supplier_file'));
        static::assertTrue($this->tableExists('akeneo_supplier_portal_supplier_product_file'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function tableExists(string $tableName): bool
    {
        return $this->connection->executeQuery(
            'SHOW TABLES LIKE :tableName', ['tableName' => $tableName]
        )->rowCount() >= 1;
    }
}
