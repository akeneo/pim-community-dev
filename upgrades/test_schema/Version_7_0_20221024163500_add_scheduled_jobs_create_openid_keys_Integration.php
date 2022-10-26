<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_7_0_20221024163500_add_scheduled_jobs_create_openid_keys_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL =  '_7_0_20221024154100_add_scheduled_jobs_create_openid_keys';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_an_instance_if_not_present(): void
    {
        $this->deleteJobInstance('create_openid_keys');
        $this->assertNull($this->jobInstanceId('create_openid_keys'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertNotNull($this->jobInstanceId('create_openid_keys'));
    }

    public function test_it_does_not_adds_an_instance_if_present(): void
    {
        $jobInstanceId = $this->jobInstanceId('create_openid_keys');
        $this->assertNotNull($jobInstanceId);
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertEquals($jobInstanceId, $this->jobInstanceId('create_openid_keys'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function deleteJobInstance(string $jobInstanceCode): void
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
        $sql = 'SELECT id FROM akeneo_batch_job_instance WHERE code = :jobCode';

        return $this->connection->executeQuery($sql, ['jobCode' => $jobCode])
            ->fetchFirstColumn()[0] ?? null;
    }
}