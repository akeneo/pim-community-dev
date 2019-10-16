<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Command;

use Akeneo\Tool\Bundle\BatchQueueBundle\Command\JobQueueConsumerCommand;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Doctrine\DBAL\Driver\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

class JobQueueConsumerCommandIntegration extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

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
    }

    public function testLaunchAJobExecution()
    {
        $jobExecution = $this->createJobExecutionInQueue('csv_product_export');

        $output = $this->jobLauncher->launchConsumerOnce();

        $standardOutput = $output->fetch();

        Assert::assertStringContainsString(sprintf('Job execution "%s" is finished.', $jobExecution->getId()), $standardOutput);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::COMPLETED, $row['status']);
        Assert::assertEquals(ExitStatus::COMPLETED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution[0]);

        Assert::assertEquals(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::COMPLETED, $jobExecution->getExitStatus()->getExitCode());

        $stmt = $this->getConnection()->prepare('SELECT consumer from akeneo_batch_job_execution_queue');
        $stmt->execute();
        $row = $stmt->fetch();

        Assert::assertNotEmpty($row['consumer']);
    }

    public function testLaunchFilteredJobExecution()
    {
        $jobExecution = $this->createJobExecutionInQueue('csv_product_export');

        $output = $this->jobLauncher->launchConsumerOnce(['-j' => ['csv_product_export']]);
        $standardOutput = $output->fetch();
        Assert::assertStringContainsString(sprintf('Job execution "%s" is finished.', $jobExecution->getId()), $standardOutput);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::COMPLETED, $row['status']);
        Assert::assertEquals(ExitStatus::COMPLETED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution[0]);

        Assert::assertEquals(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::COMPLETED, $jobExecution->getExitStatus()->getExitCode());

        $stmt = $this->getConnection()->prepare('SELECT consumer from akeneo_batch_job_execution_queue');
        $stmt->execute();
        $row = $stmt->fetch();

        Assert::assertNotEmpty($row['consumer']);
    }

    public function testStatusOfACrashedJobExecution()
    {
        $jobExecution = $this->createJobExecutionInQueue('infinite_loop_job');

        $options = ['email' => 'ziggy@akeneo.com', 'env' => $this->getParameter('kernel.environment')];
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), $options);

        $this->getQueue()->publish($jobExecutionMessage);

        $daemonProcess = $this->jobLauncher->launchConsumerOnceInBackground();

        $jobExecutionProcessPid = $this->getJobExecutionProcessPid($daemonProcess);

        // wait update of the job execution status in database
        sleep(5);

        $killJobExecution = new Process(sprintf('kill -9 %s', $jobExecutionProcessPid));
        $killJobExecution->run();
        sleep(JobQueueConsumerCommand::HEALTH_CHECK_INTERVAL + 5);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::FAILED, $row['status']);
        Assert::assertEquals(ExitStatus::FAILED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution[0]);

        Assert::assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::FAILED, $jobExecution->getExitStatus()->getExitCode());
    }

    public function testJobExecutionStatusResolverWhenDaemonAndJobExecutionCrash()
    {
        $jobExecution = $this->createJobExecutionInQueue('infinite_loop_job');

        $daemonProcess = $this->jobLauncher->launchConsumerOnceInBackground();

        $jobExecutionProcessPid = $this->getJobExecutionProcessPid($daemonProcess);

        $killDaemon = new Process(sprintf('kill -9 %s', $daemonProcess->getPid()));
        $killDaemon->run();

        // wait update of the job execution status in database
        sleep(5);

        $killJobExecution = new Process(sprintf('kill -9 %s', $jobExecutionProcessPid));
        $killJobExecution->run();

        // wait healtch check date expiration
        sleep(JobQueueConsumerCommand::HEALTH_CHECK_INTERVAL + 10);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::STARTED, $row['status']);
        Assert::assertEquals(ExitStatus::UNKNOWN, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $jobExecution = $this->get('pim_enrich.repository.job_execution')->findBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution[0]);

        Assert::assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::FAILED, $jobExecution->getExitStatus()->getExitCode());
    }

    /**
     * @param string $jobInstanceCode
     * @param string $user
     * @param array  $configuration
     *
     * @throws \RuntimeException
     *
     * @return JobExecution
     */
    protected function createJobExecution(string $jobInstanceCode, ?string $user, array $configuration = []) : JobExecution
    {
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
     *
     * @param $daemonProcess
     *
     * @throws \Exception
     *
     * @return string
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

    /**
     * @param JobExecution $jobExecution
     *
     * @return array
     */
    protected function getJobExecutionDatabaseRow(JobExecution $jobExecution): array
    {
        $stmt = $this->getConnection()->prepare('SELECT status, exit_code, health_check_time from akeneo_batch_job_execution where id = :id');
        $stmt->bindValue('id', $jobExecution->getId());
        $stmt->execute();
        $row = $stmt->fetch();

        return $row;
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection();
    }

    /**
     * @return JobExecutionQueueInterface
     */
    protected function getQueue(): JobExecutionQueueInterface
    {
        return $this->get('akeneo_batch_queue.queue.database_job_execution_queue');
    }

    /**
     * @return JobExecutionManager
     */
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
