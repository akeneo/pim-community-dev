<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Launcher;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SynchronousJobLauncher implements JobLauncherInterface
{
    /**
     * Interval in seconds before checking if the process is still running.
     */
    private const RUNNING_PROCESS_CHECK_INTERVAL = 5;

    public function __construct(
        private JobExecutionManager $executionManager,
        private JobRepositoryInterface $jobRepository,
        private JobExecutionRepository $jobExecutionRepository,
        private LoggerInterface $logger,
        private string $projectDir,
    ) {
    }

    public function launch(JobInstance $jobInstance, ?UserInterface $user, array $configuration = []): JobExecution
    {
        $jobExecution = $this->createJobExecution($jobInstance, $user, $configuration);
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), []);

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
            $this->logger->error('Job execution failed, an error occurred', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
        } finally {
            $exitStatus = $this->executionManager->getExitStatus($jobExecutionMessage);
            if ($exitStatus->isRunning()) {
                $this->executionManager->markAsFailed($jobExecution->getId());
            }
        }

        $this->logger->info('Job execution is finished', ['job_id' => $jobExecution->getId()]);

        return $this->jobExecutionRepository->find($jobExecution->getId());
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

    private function createJobExecution(JobInstance $jobInstance, ?UserInterface $user, array $jobParameters): JobExecution
    {
        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, new JobParameters($jobParameters));
        if ($user) {
            $jobExecution->setUser($user);
        }

        $this->jobRepository->updateJobExecution($jobExecution);

        return $jobExecution;
    }
}
