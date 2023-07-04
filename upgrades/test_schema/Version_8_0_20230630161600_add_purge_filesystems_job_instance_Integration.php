<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_8_0_20230630161600_add_purge_filesystems_job_instance_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230630161600_add_purge_filesystems_job_instance';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function testItAddsAnInstanceIfNotPresent()
    {
        $this->deleteJobInstance('purge_filesystems');
        $this->assertNull($this->jobInstanceId('purge_filesystems'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertNotNull($this->jobInstanceId('purge_filesystems'));
    }

    public function testItDoesNotAddsAnInstanceIfPresent()
    {
        $jobInstanceId = $this->jobInstanceId('purge_filesystems');
        $this->assertNotNull($jobInstanceId);
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertEquals($jobInstanceId, $this->jobInstanceId('purge_filesystems'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function deleteJobInstance(string $jobInstanceCode)
    {
        $this->connection->executeStatement(
            'DELETE FROM akeneo_batch_job_instance WHERE code = :job_instance_code',
            [
                'job_instance_code' => $jobInstanceCode,
            ]
        );
    }

    private function jobInstanceId(string $jobCode): ?string
    {
        $sql = 'SELECT id FROM akeneo_batch_job_instance WHERE code = :job_code';

        return $this->connection->executeQuery($sql, ['job_code' => $jobCode])
            ->fetchFirstColumn()[0] ?? null;
    }
}