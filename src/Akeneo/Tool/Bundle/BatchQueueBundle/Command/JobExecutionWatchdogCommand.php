<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * The watchdog process is launched by the daemon consuming messages to run jobs. This watchdog process  launches itself a child process to run a single Akeneo job. 
 The daemon does not run directly the jobs:
 - the daemon is tenant agnostic whereas watchdog process is dedicated for a tenant
 - if the job die for unexpected reason, the job status is updated by the watchdog, which is possible as it can access to the database (tenant specific)
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionWatchdogCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:watchdog';
    protected static $defaultDescription = '[Internal] Launched by the job queue consumer';

    /** Interval in seconds before updating health check if job is still running. */
    public const HEALTH_CHECK_INTERVAL = 5;

    /** Interval in microseconds before checking if the process is still running. */
    private const RUNNING_PROCESS_CHECK_INTERVAL = 200000;

    public function __construct(
        private JobExecutionManager $executionManager,
        private LoggerInterface $logger,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHidden(true)
            ->addArgument('job_execution_id', InputArgument::REQUIRED, 'Job execution ID')
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Username to launch the job instance with'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email to notify at the end of the job execution'
            )
            ->addOption(
                'no-log',
                null,
                InputOption::VALUE_NONE,
                "Don't display logs"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s/bin/console', $this->projectDir);
        $startTime = time();
        $jobExecutionId = (int)$input->getArgument('job_execution_id');
        try {
            $processArguments = $this->buildBatchCommand(
                $console,
                $pathFinder->find(),
                $jobExecutionId,
                $input->getOptions()
            );
            $process = new Process($processArguments);
            $process->setTimeout(null);

            $this->logger->notice('Launching job execution "{job_execution_id}".', [
                'job_execution_id' => $jobExecutionId,
            ]);
            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $this->executeProcess($process, $jobExecutionId);
        } catch (\Throwable $t) {
            $this->logger->error(
                sprintf('An error occurred: %s', $t->getMessage()),
                ['exception' => $t]
            );
        } finally {
            // update status if the job execution failed due to an uncatchable error as a fatal error
            if ($this->executionManager->getExitStatus((int)$jobExecutionId)?->isRunning()) {
                $this->executionManager->markAsFailed($jobExecutionId);
            }
        }

        $executionTimeInSec = time() - $startTime;
        $this->logger->notice('Job execution "{job_execution_id}" is finished in {execution_time_in_sec} seconds.', [
            'job_execution_id' => $jobExecutionId,
            'execution_time_in_sec' => $executionTimeInSec,
        ]);

        return Command::SUCCESS;
    }

    private function buildBatchCommand(
        string $console,
        string $phpPath,
        int $jobExecutionId,
        array $watchdogOptions
    ): array {
        $processArguments = [
            $phpPath,
            $console,
            'akeneo:batch:job',
            'dummy_batch_code',
            $jobExecutionId,
            '--quiet',
        ];

        foreach ($watchdogOptions as $optionName => $optionValue) {
            if (true === $optionValue) {
                $processArguments[] = sprintf('--%s', $optionName);
            } elseif (false !== $optionValue && null !== $optionValue) {
                $processArguments[] = sprintf('--%s=%s', $optionName, $optionValue);
            }
        }

        return $processArguments;
    }

    private function executeProcess(Process $process, int $jobExecutionId): void
    {
        $this->executionManager->updateHealthCheck($jobExecutionId);
        $process->start();

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
