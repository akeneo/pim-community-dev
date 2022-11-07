<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_7_0_20221028090000_add_scheduled_job_rule_engine_execute_rules_Integration extends TestCase
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
        $this->deleteJobInstance('rule_engine_execute_rules_scheduled');
        $this->assertNull($this->jobInstanceId('rule_engine_execute_rules_scheduled'));
        $this->reExecuteMigration($this->migrationLabel());
        $this->assertNotNull($this->jobInstanceId('rule_engine_execute_rules_scheduled'));
    }

    public function test_it_does_not_add_an_instance_if_present()
    {
        $jobInstanceId = $this->jobInstanceId('rule_engine_execute_rules_scheduled');
        $this->assertNotNull($jobInstanceId);
        $this->reExecuteMigration($this->migrationLabel());
        $this->assertEquals($jobInstanceId, $this->jobInstanceId('rule_engine_execute_rules_scheduled'));
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
        $sql = 'SELECT id FROM akeneo_batch_job_instance WHERE code = :jobCode';

        $id = $this->connection->executeQuery($sql, ['jobCode' => $jobCode])
            ->fetchFirstColumn()[0] ?? null;

        return $id ? (int) $id : null;
    }
}
