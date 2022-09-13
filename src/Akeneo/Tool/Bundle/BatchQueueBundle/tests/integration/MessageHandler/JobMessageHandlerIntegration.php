<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\MessageHandler;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchQueueBundle\MessageHandler\JobMessageHandler;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessage;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class JobMessageHandlerIntegration extends TestCase
{
    protected JobMessageHandler $jobExecutionMessageHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobExecutionMessageHandler = $this->get(JobMessageHandler::class);
    }

    public function testLaunchAJobExecution(): void
    {
        $jobExecution = $this->createJobExecution('csv_product_export', 'mary');
        $jobExecutionMessage = $this->createAndPublishJobExecutionMessageInQueue($jobExecution);

        $this->jobExecutionMessageHandler->handleJobExecution($jobExecutionMessage);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::COMPLETED, $row['status']);
        Assert::assertEquals(ExitStatus::COMPLETED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findOneBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->get('akeneo_batch_queue.manager.job_execution_manager')->resolveJobExecutionStatus(
            $jobExecution
        );

        Assert::assertEquals(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::COMPLETED, $jobExecution->getExitStatus()->getExitCode());
    }

    public function testLaunchAScheduledJob(): void
    {
        $jobCode = 'versioning_refresh';

        $jobMessage = ScheduledJobMessage::createFromNormalized([
            'job_code' => $jobCode,
            'options' => [],
        ]);

        $this->jobExecutionMessageHandler->handleScheduledJob($jobMessage);

        $row = $this->getLastJobExecutionDatabaseRow($jobCode);

        Assert::assertEquals(BatchStatus::COMPLETED, $row['status']);
        Assert::assertEquals(ExitStatus::COMPLETED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findOneBy(['id' =>$row['id']]);
        $jobExecution = $this->get('akeneo_batch_queue.manager.job_execution_manager')->resolveJobExecutionStatus(
            $jobExecution
        );

        Assert::assertEquals(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::COMPLETED, $jobExecution->getExitStatus()->getExitCode());
    }

    private function createAndPublishJobExecutionMessageInQueue(JobExecution $jobExecution
    ): JobExecutionMessageInterface {
        $jobExecutionMessage = DataMaintenanceJobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), [
            'email' => 'ziggy@akeneo.com',
            'env' => $this->getParameter('kernel.environment'),
        ]);
        $this->get('akeneo_batch_queue.queue.job_execution_queue')->publish($jobExecutionMessage);

        return $jobExecutionMessage;
    }

    protected function createJobExecution(string $jobInstanceCode, ?string $user): JobExecution
    {
        $jobInstanceClass = $this->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($jobInstanceClass)
            ->findOneBy(['code' => $jobInstanceCode]);

        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $configuration = $jobInstance->getRawParameters();

        $jobParameters = $this->get('akeneo_batch.job_parameters_factory')->create($job, $configuration);

        $errors = $this->get('akeneo_batch.job.job_parameters_validator')->validate(
            $job,
            $jobParameters,
            ['Default', 'Execution']
        );
        Assert::assertEquals(0, count($errors), 'JobExecution could not be created due to invalid job parameters.');

        $jobExecution = $this->get('akeneo_batch.job_repository')->createJobExecution(
            $job,
            $jobInstance,
            $jobParameters
        );
        $jobExecution->setUser($user);
        $this->get('akeneo_batch.job_repository')->updateJobExecution($jobExecution);

        return $jobExecution;
    }

    private function getJobExecutionDatabaseRow(JobExecution $jobExecution): array
    {
        $sql = 'SELECT status, exit_code, health_check_time from akeneo_batch_job_execution where id = :id';

        return $this->getConnection()->executeQuery($sql, ['id' => $jobExecution->getId()])->fetchAssociative();
    }

    private function getLastJobExecutionDatabaseRow(string $jobCode): array
    {
        $sql = <<< EOS
            SELECT e.id, e.status, e.exit_code, e.health_check_time 
            FROM akeneo_batch_job_execution e 
            INNER JOIN akeneo_batch_job_instance j ON e.job_instance_id = j.id 
            WHERE j.code = :job_code
        EOS;

        return $this->getConnection()->executeQuery($sql, ['job_code' => $jobCode])->fetchAssociative();
    }

    protected function getConnection(): Connection
    {
        return $this->get('doctrine.dbal.default_connection');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
