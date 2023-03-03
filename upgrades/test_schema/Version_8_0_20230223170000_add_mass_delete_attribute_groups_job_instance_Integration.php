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

final class Version_8_0_20230223170000_add_mass_delete_attribute_groups_job_instance_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230223170000_add_mass_delete_attribute_groups_job_instance';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function testItAddsAnInstanceIfNotPresent()
    {
        $this->deleteJobInstance('delete_attribute_groups');
        $this->assertNull($this->jobInstanceId('delete_attribute_groups'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertNotNull($this->jobInstanceId('delete_attribute_groups'));
    }

    public function testItDoesNotAddsAnInstanceIfPresent()
    {
        $jobInstanceId = $this->jobInstanceId('delete_attribute_groups');
        $this->assertNotNull($jobInstanceId);
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertEquals($jobInstanceId, $this->jobInstanceId('delete_attribute_groups'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function deleteJobInstance(string $jobInstanceCode): void
    {
        $this->connection->executeStatement(
            'DELETE FROM akeneo_batch_job_instance WHERE code = :job_instance_code',
            [
                'job_instance_code' => $jobInstanceCode,
            ]
        );
    }

    private function jobInstanceId(string $jobCode): ?int
    {
        $sql = 'SELECT id FROM akeneo_batch_job_instance WHERE code = :job_code';

        $jobInstanceId = $this->connection->executeQuery($sql, ['job_code' => $jobCode])->fetchOne();
        if ($jobInstanceId === false) {
            return null;
        }

        return (int) $jobInstanceId;
    }
}
