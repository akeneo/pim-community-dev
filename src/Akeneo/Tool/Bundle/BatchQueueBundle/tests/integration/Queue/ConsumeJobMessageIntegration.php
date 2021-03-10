<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Queue;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\BatchQueueBundle\MessageHandler\JobExecutionMessageHandler;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

class ConsumeJobMessageIntegration extends TestCase
{
    protected JobLauncher $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $jobInstance = new JobInstance('import', 'test', 'infinite_loop_job');
        $jobInstance->setCode('infinite_loop_job');
        $jobInstanceSaver = $this->get('akeneo_batch.saver.job_instance');
        $jobInstanceSaver->save($jobInstance);

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        // Some messages created in another test could be in the queue. To prevent that we flush the queue.
        $this->jobLauncher->flushMessengerJobQueue();
    }

    public function testLaunchAJobExecution(): void
    {
        $jobExecution = $this->createJobExecutionInQueue('csv_product_export');

        $this->jobLauncher->launchConsumerOnceUsingMessenger();

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::COMPLETED, $row['status']);
        Assert::assertEquals(ExitStatus::COMPLETED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution[0]);

        Assert::assertEquals(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::COMPLETED, $jobExecution->getExitStatus()->getExitCode());
    }

    public function testStatusOfACrashedJobExecution(): void
    {
        $jobExecution = $this->createJobExecutionInQueue('infinite_loop_job');

        $options = ['email' => 'ziggy@akeneo.com', 'env' => $this->getParameter('kernel.environment')];
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), $options);

        $this->getQueue()->publish($jobExecutionMessage);

        $daemonProcess = $this->jobLauncher->launchConsumerOnceInBackgroundUsingMessenger();

        $jobExecutionProcessPid = $this->getJobExecutionProcessPid($daemonProcess);

        sleep(5);

        $killJobExecution = new Process(sprintf('kill -9 %s', $jobExecutionProcessPid));
        $killJobExecution->run();
        sleep(JobExecutionMessageHandler::HEALTH_CHECK_INTERVAL + 5);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::FAILED, $row['status']);
        Assert::assertEquals(ExitStatus::FAILED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution[0]);

        Assert::assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::FAILED, $jobExecution->getExitStatus()->getExitCode());
    }

    public function testJobExecutionStatusResolverWhenDaemonAndJobExecutionCrash(): void
    {
        $jobExecution = $this->createJobExecutionInQueue('infinite_loop_job');

        $daemonProcess = $this->jobLauncher->launchConsumerOnceInBackgroundUsingMessenger(5);

        $jobExecutionProcessPid = $this->getJobExecutionProcessPid($daemonProcess);

        $killDaemon = new Process(sprintf('kill -9 %s', $daemonProcess->getPid()));
        $killDaemon->run();

        // wait update of the job execution status in database
        while ($daemonProcess->isRunning()) {
            sleep(1);
        }
        sleep(2);

        $killJobExecution = new Process(sprintf('kill -9 %s', $jobExecutionProcessPid));
        $killJobExecution->run();

        // wait healtch check date expiration
        sleep(JobExecutionMessageHandler::HEALTH_CHECK_INTERVAL + 2);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::STARTED, $row['status']);
        Assert::assertEquals(ExitStatus::UNKNOWN, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution[0]);

        Assert::assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::FAILED, $jobExecution->getExitStatus()->getExitCode());
    }

    protected function createJobExecution(
        string $jobInstanceCode,
        ?string $user,
        array $configuration = []
    ) : JobExecution {
        $jobInstanceClass = $this->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($jobInstanceClass)
            ->findOneBy(['code' => $jobInstanceCode]);

        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);

        $configuration = array_merge($jobInstance->getRawParameters(), $configuration);

        $jobParameters = $this->get('akeneo_batch.job_parameters_factory')->create($job, $configuration);

        $errors = $this->get('akeneo_batch.job.job_parameters_validator')->validate($job, $jobParameters, ['Default', 'Execution']);

        if (count($errors) > 0) {
            throw new \RuntimeException('JobExecution could not be created due to invalid job parameters.');
        }

        $jobExecution = $this->get('akeneo_batch.job_repository')->createJobExecution($jobInstance, $jobParameters);
        $jobExecution->setUser($user);
        $this->get('akeneo_batch.job_repository')->updateJobExecution($jobExecution);

        return $jobExecution;
    }

    private function createJobExecutionInQueue(string $jobInstanceCode): JobExecution
    {
        $jobExecution = $this->createJobExecution($jobInstanceCode, 'mary');
        $options = ['email' => 'ziggy@akeneo.com', 'env' => $this->getParameter('kernel.environment')];
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), $options);
        $this->getQueue()->publish($jobExecutionMessage);

        return $jobExecution;
    }

    /**
     * Returns the PID of the job execution process launched by the daemon process.
     */
    protected function getJobExecutionProcessPid(Process $daemonProcess): string
    {
        $count = 0;
        do {
            $pgrep = new Process(sprintf('pgrep -P %s', $daemonProcess->getPid()));
            $pgrep->run();
            $output = trim($pgrep->getOutput());
            $isJobLaunched = '' !== $output;

            $count++;
            if ($count > 30) {
                throw new \Exception('Time out to launch the job execution child process.');
            }

            sleep(1);
        } while (false === $isJobLaunched);

        return $output;
    }

    protected function getJobExecutionDatabaseRow(JobExecution $jobExecution): array
    {
        return $this->getConnection()->executeQuery(
            'SELECT status, exit_code, health_check_time from akeneo_batch_job_execution where id = :id',
            ['id' => $jobExecution->getId()]
        )->fetch();
    }

    protected function getConnection(): Connection
    {
        return $this->get('doctrine.dbal.default_connection');
    }

    protected function getQueue(): JobExecutionQueueInterface
    {
        return $this->get('akeneo_batch_queue.queue.job_execution_queue');
    }

    protected function getJobExecutionManager(): JobExecutionManager
    {
        return $this->get('akeneo_batch_queue.manager.job_execution_manager');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
