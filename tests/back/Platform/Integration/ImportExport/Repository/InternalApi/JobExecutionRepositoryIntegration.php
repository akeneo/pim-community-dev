<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\Repository\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;

class JobExecutionRepositoryIntegration extends TestCase
{
    private Connection $sqlConnection;
    private JobExecutionRepository $jobExecutionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlConnection = $this->get('database_connection');
        $this->jobExecutionRepository = $this->get('pim_enrich.repository.job_execution');
    }

    public function testItDetectsOtherExecutionRunning(): void
    {
        $this->addBaseJobExecutions();

        $jobExecutionId1 = $this->addJobExecution(new BatchStatus(BatchStatus::STARTED));
        self::assertFalse(
            $this->jobExecutionRepository->isOtherJobExecutionRunning(
                $this->jobExecutionRepository->find($jobExecutionId1)
            )
        );

        $jobExecutionId2 = $this->addJobExecution(new BatchStatus(BatchStatus::STARTED));
        $jobExecution = $this->jobExecutionRepository->find($jobExecutionId2);
        self::assertTrue(
            $this->jobExecutionRepository->isOtherJobExecutionRunning(
                $this->jobExecutionRepository->find($jobExecutionId2)
            )
        );

        $this->updateJobExecutionStatus($jobExecutionId1, new BatchStatus(BatchStatus::COMPLETED));
        self::assertFalse(
            $this->jobExecutionRepository->isOtherJobExecutionRunning($jobExecution)
        );
    }

    public function testItDetectsOtherExecutionRunningWithinHealthCheckTimeLimit(): void
    {
        $this->addBaseJobExecutions();

        $jobExecutionId1 = $this->addJobExecution(new BatchStatus(BatchStatus::STARTED));
        self::assertFalse(
            $this->jobExecutionRepository->isOtherJobExecutionRunning(
                $this->jobExecutionRepository->find($jobExecutionId1)
            )
        );

        $jobExecutionId2 = $this->addJobExecution(new BatchStatus(BatchStatus::STARTED));

        // wait less than the health check limit
        sleep(2);

        self::assertTrue(
            $this->jobExecutionRepository->isOtherJobExecutionRunning(
                $this->jobExecutionRepository->find($jobExecutionId2)
            )
        );

        // wait a bit more
        sleep(JobExecutionRepository::HEALTH_CHECK_INTERVAL);

        self::assertFalse(
            $this->jobExecutionRepository->isOtherJobExecutionRunning(
                $this->jobExecutionRepository->find($jobExecutionId2)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function addBaseJobExecutions(): void
    {
        $JobInstanceId = $this->sqlConnection->executeQuery(
            'SELECT id FROM akeneo_batch_job_instance WHERE code = "csv_product_import";'
        )->fetchOne();

        $insertJobExecution = <<<SQL
        INSERT INTO `akeneo_batch_job_execution` 
            (job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
        VALUES 
            (:job_instance_id, null, 'admin', :status, null, null, '2022-10-16 09:38:16', null, null, 'COMPLETED', '', 'a:0:{}', null, '{}');
        SQL;
        $this->sqlConnection->executeStatement(
            $insertJobExecution,
            [
                'job_instance_id' => $JobInstanceId,
                'status' => BatchStatus::COMPLETED,
            ]
        );

        $insertJobExecution = <<<SQL
        INSERT INTO `akeneo_batch_job_execution` 
            (job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
        VALUES 
            (:job_instance_id, null, 'admin', :status, null, null, '2022-10-16 09:50:16', null, null, 'FAILED', '', 'a:0:{}', null, '{}');
        SQL;
        $this->sqlConnection->executeStatement(
            $insertJobExecution,
            [
                'job_instance_id' => $JobInstanceId,
                'status' => BatchStatus::FAILED,
            ]
        );
    }

    private function addJobExecution(BatchStatus $status): int
    {
        $JobInstanceId = $this->sqlConnection->executeQuery(
            'SELECT id FROM akeneo_batch_job_instance WHERE code = "csv_product_import";'
        )->fetchOne();

        $insertJobExecution = <<<SQL
        INSERT INTO `akeneo_batch_job_execution` 
            (job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
        VALUES 
            (:job_instance_id, null, 'admin', :status, null, null, '2022-10-16 09:38:16', null, :health_check_time, 'COMPLETED', '', 'a:0:{}', null, '{}');
        SQL;
        $this->sqlConnection->executeStatement(
            $insertJobExecution,
            [
                'job_instance_id' => $JobInstanceId,
                'status' => $status->getValue(),
                'health_check_time' => (new \DateTime('now', new \DateTimeZone('UTC')))->format("Y-m-d H:i:s"),
            ]
        );

        return (int)$this->sqlConnection->lastInsertId();
    }

    private function updateJobExecutionStatus(int $jobExecutionId, BatchStatus $status): void
    {
        $sql = <<<SQL
        UPDATE akeneo_batch_job_execution 
        SET status = :status, health_check_time = :health_check_time 
        WHERE id = :id;
        SQL;
        $this->sqlConnection->executeStatement(
            $sql,
            [
                'id' => $jobExecutionId,
                'status' => $status->getValue(),
                'health_check_time' => (new \DateTime('now', new \DateTimeZone('UTC')))->format("Y-m-d H:i:s"),
            ]
        );
    }
}
