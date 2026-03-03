<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_8_0_20230124095459_add_scheduled_job_delete_expired_tokens_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230124095459_add_scheduled_job_delete_expired_tokens';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_an_instance_if_not_present()
    {
        $this->deleteJobInstance('delete_expired_tokens');
        $this->assertFalse($this->jobInstanceExists('delete_expired_tokens'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->jobInstanceExists('delete_expired_tokens'));
    }

    public function test_it_does_not_add_an_instance_if_present()
    {
        $this->assertTrue($this->jobInstanceExists('delete_expired_tokens'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->jobInstanceExists('delete_expired_tokens'));
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

    private function jobInstanceExists(string $jobInstanceCode): bool
    {
        $id = $this->connection->fetchOne(
            'SELECT id FROM akeneo_batch_job_instance WHERE code = :job_instance_code',
            ['job_instance_code' => $jobInstanceCode]
        );

        return $id !== false;
    }
}
