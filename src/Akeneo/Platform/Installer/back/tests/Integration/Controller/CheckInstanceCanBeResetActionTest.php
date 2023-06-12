<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\Controller;

use Akeneo\Platform\Installer\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CheckInstanceCanBeResetActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'akeneo_installer_check_reset_instance';

    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');

        $this->logAs('julia');
        $this->featureFlags->enable('reset_pim');
    }

    public function test_it_returns_ok_when_no_job_is_queued_or_running(): void
    {
        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    public function test_it_returns_failure_when_a_job_is_queued(): void
    {
        $this->given_a_job_with_status(BatchStatus::STARTING);

        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    public function test_it_returns_failure_when_a_job_is_running(): void
    {
        $this->given_a_job_with_status(BatchStatus::STARTED);

        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    private function given_a_job_with_status(int $status): void
    {
        $jobInstanceId = $this->connection->executeQuery('SELECT id FROM akeneo_batch_job_instance WHERE code = "csv_product_quick_export";')->fetchOne();
        $insertJobExecution = <<<SQL
INSERT INTO `akeneo_batch_job_execution` (job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
VALUES (:job_instance_id, null, 'admin', $status, null, null, '2020-10-16 09:38:16', null, null, 'UNKNOWN', '', 'a:0:{}', null, '{}');
SQL;
        $this->connection->executeUpdate($insertJobExecution, ['job_instance_id' => $jobInstanceId]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
