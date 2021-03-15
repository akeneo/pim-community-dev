<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\MessageHandler;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchQueueBundle\MessageHandler\JobExecutionMessageHandler;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\BatchQueue\Queue\BackendJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class JobExecutionMessageHandlerIntegration extends TestCase
{
    protected JobExecutionMessageHandler $jobExecutionMessageHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobExecutionMessageHandler = $this->get(JobExecutionMessageHandler::class);
    }

    public function testLaunchAJobExecution(): void
    {
        $jobExecution = $this->createJobExecution('csv_product_export', 'mary');
        $jobExecutionMessage = $this->createAndPublishJobExecutionMessageInQueue($jobExecution);

        $this->jobExecutionMessageHandler->__invoke($jobExecutionMessage);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::COMPLETED, $row['status']);
        Assert::assertEquals(ExitStatus::COMPLETED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findOneBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->get('akeneo_batch_queue.manager.job_execution_manager')->resolveJobExecutionStatus($jobExecution);

        Assert::assertEquals(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::COMPLETED, $jobExecution->getExitStatus()->getExitCode());
    }

    private function createAndPublishJobExecutionMessageInQueue(JobExecution $jobExecution): JobExecutionMessageInterface
    {
        $jobExecutionMessage = BackendJobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), [
            'email' => 'ziggy@akeneo.com',
            'env' => $this->getParameter('kernel.environment'),
        ]);
        $this->get('akeneo_batch_queue.queue.database_job_execution_queue')->publish($jobExecutionMessage);

        return $jobExecutionMessage;
    }

    protected function createJobExecution(string $jobInstanceCode, ?string $user) : JobExecution
    {
        $jobInstanceClass = $this->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($jobInstanceClass)
            ->findOneBy(['code' => $jobInstanceCode]);

        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $configuration = $jobInstance->getRawParameters();

        $jobParameters = $this->get('akeneo_batch.job_parameters_factory')->create($job, $configuration);

        $errors = $this->get('akeneo_batch.job.job_parameters_validator')->validate($job, $jobParameters, ['Default', 'Execution']);
        Assert::assertEquals(0, count($errors), 'JobExecution could not be created due to invalid job parameters.');

        $jobExecution = $this->get('akeneo_batch.job_repository')->createJobExecution($jobInstance, $jobParameters);
        $jobExecution->setUser($user);
        $this->get('akeneo_batch.job_repository')->updateJobExecution($jobExecution);

        return $jobExecution;
    }

    private function getJobExecutionDatabaseRow(JobExecution $jobExecution): array
    {
        $sql = 'SELECT status, exit_code, health_check_time from akeneo_batch_job_execution where id = :id';

        return $this->getConnection()->executeQuery($sql, ['id' => $jobExecution->getId()])->fetch();
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
