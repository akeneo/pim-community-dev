<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute\GetBlacklistedAttributeJobExecutionId;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class GetBlacklistedAttributeJobExecutionIdIntegration extends TestCase
{
    private Connection $sqlConnection;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlConnection = $this->get('database_connection');
    }

    public function test_it_returns_null_if_the_attribute_code_is_not_blacklisted(): void
    {
        $query = $this->getQuery();
        $result = $query->forAttributeCode('UNKNOWN');

        $this->assertNull($result);
    }

    public function test_it_returns_the_job_execution_id_of_a_blacklisted_attribute_code(): void
    {
        $jobExecutionId = $this->getJobExecutionId();

        $blacklister = $this->getBlacklister();
        $blacklister->blacklist('description');
        $blacklister->registerJob('description', $jobExecutionId);

        $query = $this->getQuery();
        $result = $query->forAttributeCode('description');

        $this->assertEquals($jobExecutionId, $result);
    }

    private function getJobExecutionId(): int
    {
        $jobInstanceId = $this->sqlConnection->executeQuery('SELECT id FROM akeneo_batch_job_instance WHERE code = "clean_removed_attribute_job";')->fetchColumn();
        $insertJobExecution = <<<SQL
INSERT INTO `akeneo_batch_job_execution` (job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
VALUES (:job_instance_id, null, 'admin', 2, null, null, '2020-10-16 09:38:16', null, null, 'UNKNOWN', '', 'a:0:{}', null, '{}');
SQL;
        $this->sqlConnection->executeUpdate($insertJobExecution, ['job_instance_id' => $jobInstanceId]);

        return (int) $this->sqlConnection->lastInsertId();
    }

    private function getQuery(): GetBlacklistedAttributeJobExecutionId
    {
        return $this->get('akeneo.pim.structure.query.get_blacklisted_attribute_job_execution_id');
    }

    private function getBlacklister(): AttributeCodeBlacklister
    {
        return $this->get('pim_catalog.manager.attribute_code_blacklister');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
