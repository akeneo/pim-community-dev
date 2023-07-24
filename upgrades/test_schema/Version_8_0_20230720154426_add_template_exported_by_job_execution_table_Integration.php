<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/** @group migration-supplier-portal */
final class Version_8_0_20230720154426_add_template_exported_by_job_execution_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230720154426_add_template_exported_by_job_execution_table';
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_creates_template_exported_by_job_execution_table()
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->tableExists('akeneo_supplier_portal_template_exported_by_job_execution'));
    }

    private function tableExists(string $tableName): bool
    {
        return 1 === $this->connection->executeQuery(
                'SHOW TABLES LIKE :tableName',
                ['tableName' => $tableName]
            )->rowCount();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
