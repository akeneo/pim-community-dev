<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_7_0_20221028090000_add_scheduled_job_connectivity_create_openid_keys_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_an_instance_if_not_present()
    {
        $this->deleteJobInstance('connectivity_create_openid_keys');
        $this->assertNull($this->jobInstanceId('connectivity_create_openid_keys'));
        $this->reExecuteMigration($this->migrationLabel());
        $this->assertNotNull($this->jobInstanceId('connectivity_create_openid_keys'));
    }

    public function test_it_does_not_adds_an_instance_if_present()
    {
        $jobInstanceId = $this->jobInstanceId('connectivity_create_openid_keys');
        $this->assertNotNull($jobInstanceId);
        $this->reExecuteMigration($this->migrationLabel());
        $this->assertEquals($jobInstanceId, $this->jobInstanceId('connectivity_create_openid_keys'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function migrationLabel(): string
    {
        if (!preg_match('/Version(_[^\\\]+)_Integration/', self::class, $match)) {
            throw new \RuntimeException('Unable to find migration label.');
        }
        return $match[1];
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
