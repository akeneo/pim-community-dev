<?php

declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_7_0_20220823150400_add_supplier_portal_supplier_product_files_clean_job_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220823150400_add_supplier_portal_supplier_product_files_clean_job';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_adds_an_instance_of_the_supplier_portal_supplier_product_files_clean_job_if_not_present()
    {
        $this->deleteJobInstance();
        $this->assertFalse($this->jobInstanceExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->jobInstanceExists());
    }

    /** @test */
    public function it_does_not_add_an_instance_of_the_supplier_portal_supplier_product_files_clean_job_if_present()
    {
        $this->assertFalse($this->jobInstanceExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->jobInstanceExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function deleteJobInstance()
    {
        $this->connection->executeStatement(
            "DELETE FROM akeneo_batch_job_instance WHERE code = :job_instance_code",
            [
                'job_instance_code' => 'supplier_portal_supplier_product_files_clean',
            ]
        );
    }

    private function jobInstanceExists(): bool
    {
        $sql = <<<SQL
            SELECT id
            FROM akeneo_batch_job_instance
            WHERE code = :jobCode
        SQL;

        return 0 !== $this->connection->executeQuery(
                $sql,
                ['jobCode' => 'supplier_portal_supplier_product_files_clean']
            )->rowCount();
    }
}
