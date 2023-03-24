<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_8_0_20230126141000_add_mass_delete_attributes_jobs_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230126141000_add_mass_delete_attributes_jobs';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_an_instance_if_not_present()
    {
        $this->deleteJobInstance('delete_attributes');
        $this->assertNull($this->jobInstanceId('delete_attributes'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertNotNull($this->jobInstanceId('delete_attributes'));
    }

    public function test_it_does_not_adds_an_instance_if_present()
    {
        $jobInstanceId = $this->jobInstanceId('delete_attributes');
        $this->assertNotNull($jobInstanceId);
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertEquals($jobInstanceId, $this->jobInstanceId('delete_attributes'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function deleteJobInstance(string $jobInstanceCode)
    {
        $this->connection->executeStatement(
            "DELETE FROM akeneo_batch_job_instance WHERE code = :job_instance_code",
            [
                'job_instance_code' => $jobInstanceCode,
            ]
        );
    }

    private function jobInstanceId(string $jobCode): ?int
    {
        $sql = 'SELECT id FROM akeneo_batch_job_instance WHERE code = :job_code';

        return $this->connection->executeQuery($sql, ['job_code' => $jobCode])
            ->fetchFirstColumn()[0] ?? null;
    }
}
