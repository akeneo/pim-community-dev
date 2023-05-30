<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\tests\Integration\EventListener;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PauseJobOnSigtermSubscriberIntegration extends TestCase
{
    private const PAUSED_JOB_EXECUTION_ID = 1;
    private const STARTED_JOB_EXECUTION_ID = 2;

    private FeatureFlags $featureFlags;
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->featureFlags = $this->get('feature_flags');
        $this->connection = $this->get('database_connection');
        $this->eventDispatcher = $this->get('event_dispatcher');
        $this->entityManager = $this->get('doctrine.orm.default_entity_manager');
        $this->createJobExecutions();
    }

    public function test_it_does_nothing_when_feature_flag_is_not_enabled(): void
    {
        $jobExecution = $this->getJobExecution(self::STARTED_JOB_EXECUTION_ID);
        $this->dispatchJobExecutionEvent($jobExecution);
        $this->assertJobExecutionHasStatus($jobExecution, BatchStatus::STARTED);
    }

    public function test_it_does_nothing_if_the_job_is_not_running(): void
    {
        $this->featureFlags->enable('pause_jobs');
        $jobExecution = $this->getJobExecution(self::PAUSED_JOB_EXECUTION_ID);
        $this->dispatchJobExecutionEvent($jobExecution);
        $this->assertJobExecutionHasStatus($jobExecution, BatchStatus::PAUSED);
    }

    public function test_it_updates_status_when_the_sigterm_is_received(): void
    {
        $this->featureFlags->enable('pause_jobs');
        $jobExecution = $this->getJobExecution(self::STARTED_JOB_EXECUTION_ID);
        $this->dispatchJobExecutionEvent($jobExecution);

        posix_kill(posix_getpid(), SIGTERM);

        $this->assertJobExecutionHasStatus($jobExecution, BatchStatus::PAUSING);
    }

    public function test_it_does_not_update_status_when_the_job_is_already_paused(): void
    {
        $this->featureFlags->enable('pause_jobs');
        $jobExecution = $this->getJobExecution(self::STARTED_JOB_EXECUTION_ID);
        $this->dispatchJobExecutionEvent($jobExecution);

        posix_kill(posix_getpid(), SIGTERM);

        $this->assertJobExecutionHasStatus($jobExecution, BatchStatus::PAUSING);

        $this->pauseJobExecution($jobExecution);

        posix_kill(posix_getpid(), SIGTERM);

        $this->assertJobExecutionHasStatus($jobExecution, BatchStatus::PAUSED);
    }

    private function assertJobExecutionHasStatus(JobExecution $jobExecution, int $status): void
    {
        $sql = <<<SQL
SELECT id
FROM akeneo_batch_job_execution
WHERE id = :job_execution_id AND status = :status
SQL;

        $result = $this->connection->executeQuery($sql, ['job_execution_id' => $jobExecution->getId(), 'status' => $status]);

        $this->assertSame(1, $result->rowCount(), sprintf('Job execution with id %d does not have the expected status %d', $jobExecution->getId(), $status));
    }

    private function getJobExecution(int $id): JobExecution
    {
        return $this->entityManager->find(JobExecution::class, $id);
    }

    private function pauseJobExecution(JobExecution $jobExecution): void
    {
        $jobExecution->setStatus(new BatchStatus(BatchStatus::PAUSED));
        $this->entityManager->persist($jobExecution);
        $this->entityManager->flush();
    }

    private function dispatchJobExecutionEvent(JobExecution $jobExecution): void
    {
        $event = new JobExecutionEvent($jobExecution);
        $this->eventDispatcher->dispatch($event, EventInterface::BEFORE_JOB_EXECUTION);
    }

    private function createJobExecutions(): void
    {
        $insertJobInstanceQuery = <<<SQL
            INSERT INTO akeneo_batch_job_instance (id, code, job_name, status, connector, raw_parameters, type)
            VALUES 
            (1, 'a_job', '', 0, '', '', '')
SQL;

        $this->connection->executeQuery($insertJobInstanceQuery);

        $insertJobExecutionQuery = <<<SQL
            INSERT INTO akeneo_batch_job_execution (id, job_instance_id, status, raw_parameters) 
            VALUES 
            (:paused_job_execution_id, 1, :paused_status, '{}'), 
            (:started_job_execution_id, 1, :started_status, '{}')
SQL;

        $this->connection->executeQuery($insertJobExecutionQuery, [
            'paused_job_execution_id' => self::PAUSED_JOB_EXECUTION_ID,
            'started_job_execution_id' => self::STARTED_JOB_EXECUTION_ID,
            'paused_status' => BatchStatus::PAUSED,
            'started_status' => BatchStatus::STARTED,
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
