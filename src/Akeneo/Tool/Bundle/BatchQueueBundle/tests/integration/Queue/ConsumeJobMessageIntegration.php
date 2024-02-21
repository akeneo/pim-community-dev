<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Queue;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchQueueBundle\Command\JobExecutionWatchdogCommand;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

class ConsumeJobMessageIntegration extends TestCase
{
    private JobLauncher $jobLauncher;
    private JobExecutionRepository $jobExecutionRepository;
    private EntityManager $em;

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

        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobExecutionRepository = $this->get('pim_enrich.repository.job_execution');
    }

    public function testLaunchAJobExecution(): void
    {
        $jobExecution = $this->createJobExecutionInQueue('csv_product_export');

        $this->jobLauncher->launchConsumerOnce();

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::COMPLETED, $row['status']);
        Assert::assertEquals(ExitStatus::COMPLETED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $this->jobExecutionRepository->clear();
        $jobExecution = $this->jobExecutionRepository->findBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution[0]);

        Assert::assertEquals(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::COMPLETED, $jobExecution->getExitStatus()->getExitCode());
    }

    public function testStatusOfACrashedJobExecution(): void
    {
        $jobExecution = $this->createJobExecutionInQueue('infinite_loop_job');

        $daemonProcess = $this->jobLauncher->launchConsumerOnceInBackground();

        $watchdogProcessPid = $this->getSubprocessPid($daemonProcess->getPid());
        $batchCommandPid = $this->getSubprocessPid($watchdogProcessPid);

        sleep(5);

        $killJobExecution = new Process(['kill', '-9', $batchCommandPid]);
        $killJobExecution->run();
        sleep(JobExecutionWatchdogCommand::HEALTH_CHECK_INTERVAL + 5);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::FAILED, $row['status']);
        Assert::assertEquals(ExitStatus::FAILED, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $this->em->clear(JobExecution::class);
        $jobExecution = $this->jobExecutionRepository->findOneBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution);

        Assert::assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::FAILED, $jobExecution->getExitStatus()->getExitCode());
    }

    /**
     * The UI must return a "failed" status when all the processes went down, like because of a pod crash
     */
    public function testJobExecutionStatusResolverWhenAllProcessesCrash(): void
    {
        $jobExecution = $this->createJobExecutionInQueue('infinite_loop_job');

        $daemonProcess = $this->jobLauncher->launchConsumerOnceInBackground();

        $watchdogProcessPid = $this->getSubprocessPid($daemonProcess->getPid());
        $batchJobCommandPid = $this->getSubprocessPid($watchdogProcessPid);

        $daemonProcess->stop(3, 9);

        // wait update of the job execution status in database
        while ($daemonProcess->isRunning()) {
            sleep(1);
        }
        sleep(2);

        $killWtachdog = new Process(['kill', '-9', $watchdogProcessPid]);
        $killWtachdog->run();
        $killJobExecution = new Process(['kill', '-9', $batchJobCommandPid]);
        $killJobExecution->run();

        // wait healtch check date expiration
        sleep(JobExecutionWatchdogCommand::HEALTH_CHECK_INTERVAL + 10);

        $row = $this->getJobExecutionDatabaseRow($jobExecution);

        Assert::assertEquals(BatchStatus::STARTED, $row['status']);
        Assert::assertEquals(ExitStatus::UNKNOWN, $row['exit_code']);
        Assert::assertNotNull($row['health_check_time']);

        $this->em->clear(JobExecution::class);
        $jobExecution = $this->jobExecutionRepository->findOneBy(['id' => $jobExecution->getId()]);
        $jobExecution = $this->getJobExecutionManager()->resolveJobExecutionStatus($jobExecution);

        Assert::assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());
        Assert::assertEquals(ExitStatus::FAILED, $jobExecution->getExitStatus()->getExitCode());
    }

    protected function createJobExecution(
        string $jobInstanceCode,
        ?string $user,
        array $configuration = []
    ): JobExecution {
        $jobInstanceClass = $this->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($jobInstanceClass)
            ->findOneBy(['code' => $jobInstanceCode]);

        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);

        $configuration = array_merge($jobInstance->getRawParameters(), $configuration);

        $jobParameters = $this->get('akeneo_batch.job_parameters_factory')->create($job, $configuration);

        $errors = $this->get('akeneo_batch.job.job_parameters_validator')->validate(
            $job,
            $jobParameters,
            ['Default', 'Execution']
        );

        if (count($errors) > 0) {
            throw new \RuntimeException('JobExecution could not be created due to invalid job parameters.');
        }

        $jobExecution = $this->get('akeneo_batch.job_repository')->createJobExecution(
            $job,
            $jobInstance,
            $jobParameters
        );
        $jobExecution->setUser($user);
        $this->get('akeneo_batch.job_repository')->updateJobExecution($jobExecution);

        return $jobExecution;
    }

    private function createJobExecutionInQueue(string $jobInstanceCode): JobExecution
    {
        $jobExecution = $this->createJobExecution($jobInstanceCode, 'mary');
        $options = ['email' => 'ziggy@akeneo.com', 'env' => $this->getParameter('kernel.environment')];
        $jobExecutionMessage = DataMaintenanceJobExecutionMessage::createJobExecutionMessage(
            $jobExecution->getId(),
            $options
        );
        $this->getQueue()->publish($jobExecutionMessage);

        return $jobExecution;
    }

    /**
     * Returns the child PID of a process.
     */
    protected function getSubprocessPid(int $processPid): int
    {
        $count = 0;
        do {
            $pgrep = new Process(['pgrep', '-P', $processPid]);
            $pgrep->run();
            $output = trim($pgrep->getOutput());
            if ('' !== $output) {
                return (int)$output;
            }

            $count++;
            if ($count > 30) {
                throw new \Exception('Time out to launch the job execution child process.');
            }

            sleep(1);
        } while (true);
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
