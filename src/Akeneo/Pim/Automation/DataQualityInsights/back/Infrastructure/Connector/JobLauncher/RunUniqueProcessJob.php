<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\AnotherJobStillRunningException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\JobNotFoundException;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

final class RunUniqueProcessJob
{
    /** Interval in seconds before checking if the process is still running. */
    private const RUNNING_PROCESS_CHECK_INTERVAL = 5;

    /** Time for which a job execution is considered as outdated. */
    private const OUTDATED_JOB_EXECUTION_TIME = '-3 HOUR';

    /** @var EntityManager */
    private $entityManager;

    /** @var JobExecutionManager */
    private $executionManager;

    /** @var JobRepositoryInterface */
    private $jobRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $projectDir;

    public function __construct(
        EntityManager $entityManager,
        JobExecutionManager $executionManager,
        JobRepositoryInterface $jobRepository,
        LoggerInterface $logger,
        string $projectDir
    ) {
        $this->entityManager = $entityManager;
        $this->executionManager = $executionManager;
        $this->jobRepository = $jobRepository;
        $this->logger = $logger;
        $this->projectDir = $projectDir;
    }

    public function run(string $jobName, \Closure $buildJobParameters)
    {
        $jobInstance = $this->getJobInstance($jobName);

        $this->ensureNoOtherJobExecutionIsRunning($jobInstance);

        $jobExecution = $this->createJobExecution($jobInstance, $buildJobParameters);
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), []);
        $this->logger->info('Job execution "{job_id}" is starting', ['message' => 'job_execution_started', 'job_id' => $jobExecution->getId()]);

        try {
            $this->executionManager->updateHealthCheck($jobExecutionMessage);
            $process = $this->initializeProcess($jobInstance, $jobExecution);
            $process->start();

            while ($process->isRunning()) {
                sleep(self::RUNNING_PROCESS_CHECK_INTERVAL);
                $this->executionManager->updateHealthCheck($jobExecutionMessage);
                $this->writeProcessOutput($process);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Job execution "{job_id}" failed, an error occurred: {error_message}', [
                'message' => 'job_execution_failed',
                'job_id' => $jobExecutionMessage->getJobExecutionId(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
        } finally {
            // update status if the job execution failed due to an uncaught error as a fatal error
            $exitStatus = $this->executionManager->getExitStatus($jobExecutionMessage);
            if ($exitStatus->isRunning()) {
                $this->executionManager->markAsFailed($jobExecutionMessage);
            }
        }

        $this->logger->info('Job execution "{job_id}" is finished', ['message' => 'job_execution_finished', 'job_id' => $jobExecutionMessage->getJobExecutionId()]);
    }

    private function initializeProcess(JobInstance $jobInstance, JobExecution $jobExecution): Process
    {
        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        $process = new Process([
            $pathFinder->find(),
            $console,
            'akeneo:batch:job',
            $jobInstance->getCode(),
            $jobExecution->getId()
        ]);
        $process->setTimeout(null);

        return $process;
    }

    private function writeProcessOutput(Process $process): void
    {
        $this->logger->info($process->getIncrementalOutput());

        $errors = $process->getIncrementalErrorOutput();
        if ($errors) {
            $this->logger->error($errors);
        }
    }

    private function ensureNoOtherJobExecutionIsRunning(JobInstance $jobInstance): void
    {
        $jobExecutionRunning = $this->entityManager
            ->getRepository(JobExecution::class)
            ->findOneBy([
                'jobInstance' => $jobInstance->getId(),
                'exitCode' => [ExitStatus::EXECUTING, ExitStatus::UNKNOWN]
            ]);

        if (null === $jobExecutionRunning) {
            return;
        }

        $this->logger->warning('Another job execution is still running (id = {job_id})', ['message' => 'another_job_execution_is_still_running', 'job_id' => $jobExecutionRunning->getId()]);

        // In case of an old job execution that has not been marked as failed.
        if ($jobExecutionRunning->getUpdatedTime() < new \DateTime(self::OUTDATED_JOB_EXECUTION_TIME)) {
            $this->logger->info('Job execution "{job_id}" is outdated: let\'s mark it has failed.', ['message' => 'job_execution_outdated', 'job_id' => $jobExecutionRunning->getId()]);
            $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage(intval($jobExecutionRunning->getId()), []);
            $this->executionManager->markAsFailed($jobExecutionMessage);
        }

        throw new AnotherJobStillRunningException();
    }

    private function getJobInstance(string $name): JobInstance
    {
        $jobInstance = $this->entityManager
            ->getRepository(JobInstance::class)
            ->findOneBy(['code' => $name]);

        if (!$jobInstance instanceof JobInstance) {
            throw new JobNotFoundException($name);
        }

        return $jobInstance;
    }

    private function createJobExecution(JobInstance $jobInstance, \Closure $buildJobParameters): JobExecution
    {
        $lastJobExecution = $this->jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED);
        $jobParameters = $buildJobParameters($lastJobExecution);

        if (!is_array($jobParameters)) {
            $jobParameters = [];
        }

        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, new JobParameters($jobParameters));

        $jobExecution->setUser(UserInterface::SYSTEM_USER_NAME);
        $this->jobRepository->updateJobExecution($jobExecution);

        return $jobExecution;
    }
}
