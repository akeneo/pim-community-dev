<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Push a registered job instance to execute into the job execution queue.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TenantWatchdogLauncherCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:watchdog';
    protected static $defaultDescription = 'Run an Akeneo batch job in a watchdog loop';

    /** Interval in seconds before updating health check if job is still running. */
    public const HEALTH_CHECK_INTERVAL = 5;
    /** Interval in microseconds before checking if the process is still running. */
    private const RUNNING_PROCESS_CHECK_INTERVAL = 200000;

    public function __construct(
        private LoggerInterface $logger,
        private JobExecutionManager $executionManager,
        private string $projectDir
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('execution_id', InputArgument::REQUIRED, 'Job execution ID');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        $executionId = (int) $input->getArgument('execution_id');

        $startTime = time();
        try {
            $arguments = [
                $pathFinder->find(),
                $console,
                'akeneo:batch:job',
                '--quiet',
                'daemon_job',
                $executionId,
            ];

            $process = new Process($arguments);
            $process->setTimeout(null);

            $this->logger->notice('Launching job execution "{job_execution_id}".', [
                'job_execution_id' => $executionId,
            ]);
            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $this->executeProcess($process, $executionId);
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()));
            $this->logger->error($t->getTraceAsString());
        } finally {
            // update status if the job execution failed due to an uncatchable error as a fatal error
            $exitStatus = $this->executionManager->getExitStatus($executionId);
            if ($exitStatus && $exitStatus->isRunning()) {
                $this->executionManager->markAsFailed($executionId);
            }
        }

        $executionTimeInSec = time() - $startTime;
        $this->logger->notice('Job execution "{job_execution_id}" is finished in {execution_time_in_sec} seconds.', [
            'job_execution_id' => $executionId,
            'execution_time_in_sec' => $executionTimeInSec,
        ]);

        return Command::SUCCESS;
    }

    private function executeProcess(Process $process, int $jobExecutionId): void
    {
        $this->executionManager->updateHealthCheck($jobExecutionId);
        $env = [];
        $process->start(null, $env);

        $nbIterationBeforeUpdatingHealthCheck = self::HEALTH_CHECK_INTERVAL * 1000000 / self::RUNNING_PROCESS_CHECK_INTERVAL;
        $iteration = 1;
        while ($process->isRunning()) {
            if ($iteration < $nbIterationBeforeUpdatingHealthCheck) {
                $iteration++;
                usleep(self::RUNNING_PROCESS_CHECK_INTERVAL);

                continue;
            }

            $this->writeProcessOutput($process);
            $this->executionManager->updateHealthCheck($jobExecutionId);
            $iteration = 1;
        }

        $this->writeProcessOutput($process);
    }

    private function writeProcessOutput(Process $process): void
    {
        $errors = $process->getIncrementalErrorOutput();
        if ($errors) {
            $this->logger->error($errors);
        }
    }

}
