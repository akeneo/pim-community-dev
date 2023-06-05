<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * The watchdog process is launched by the daemon consuming messages to run jobs. This watchdog process  launches
 * itself a child process to run a single Akeneo job. The daemon does not run directly the jobs:
 * - the daemon is tenant agnostic whereas watchdog process is dedicated for a tenant
 * - if the job die for unexpected reason, the job status is updated by the watchdog, which is possible as it can
 * access to the database (tenant specific)
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
        private readonly JobExecutionManager $executionManager,
        private readonly LoggerInterface $logger,
        private readonly string $projectDir,
        private readonly CreateJobExecutionHandlerInterface $createJobExecutionHandler,
        protected readonly LockFactory $lockFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHidden(true)
            ->addOption(
                'job_execution_id',
                null,
                InputOption::VALUE_REQUIRED,
                'Job execution ID to launch'
            )
            ->addOption(
                'job_code',
                null,
                InputOption::VALUE_REQUIRED,
                'Job code to launch when no execution id provided'
            )
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Job configuration overriding default config'
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Username to launch the job instance with'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
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
        $jobExecutionId = $input->getOption('job_execution_id') ? (int) $input->getOption('job_execution_id') : null;
        $jobCode = $input->getOption('job_code') ? (string) $input->getOption('job_code') : null;

        if (null === $jobExecutionId && null === $jobCode) {
            throw new \InvalidArgumentException('You must specify job_execution_id or job_code');
        }
        if (null === $jobExecutionId) {
            $jobConfiguration = $input->getOption('config') ? \json_decode($input->getOption('config'), true) : [];
            $jobExecution = $this->createJobExecutionHandler->createFromBatchCode($jobCode, $jobConfiguration, null);
            $jobExecutionId = $jobExecution->getId();
            $this->logger->info(
                'Created job execution "{job_execution_id}" for job "{job_code}" with configuration {configuration}',
                [
                    'job_execution_id' => $jobExecutionId,
                    'job_code' => $jobCode,
                    'configuration' => \json_encode($jobConfiguration),
                ]
            );
        }

        $console = sprintf('%s/bin/console', $this->projectDir);
        $pathFinder = new PhpExecutableFinder();
        $startTime = time();
        try {
            $processArguments = $this->buildBatchCommand(
                $console,
                $pathFinder->find(),
                $jobCode,
                $jobExecutionId,
                $input->getOptions() ?? [],
            );
            $process = new Process($processArguments);
            $process->setTimeout(null);

            $this->logger->info(
                'Launching job execution "{job_execution_id}" for job "{job_code}"',
                [
                    'job_execution_id' => $jobExecutionId,
                    'job_code' => $jobCode,
                ]
            );
            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $this->executeProcess($process, $jobExecutionId);
        } catch (\Throwable $t) {
            $this->logger->error(
                sprintf('An error occurred: %s', $t->getMessage()),
                ['exception' => $t]
            );
        } finally {
            // update status if the job execution failed due to an uncatchable error as a fatal error
            if ($this->executionManager->getExitStatus((int) $jobExecutionId)?->isRunning()) {
                $this->executionManager->markAsFailed($jobExecutionId);
            }
            $this->releaseJobLock((int) $jobExecutionId);
        }

        $executionTimeInSec = time() - $startTime;
        $this->logger->info(
            'Job execution "{job_execution_id}" is finished in {execution_time_in_sec} seconds.',
            [
                'job_execution_id' => $jobExecutionId,
                'execution_time_in_sec' => $executionTimeInSec,
            ]
        );

        return Command::SUCCESS;
    }

    private function buildBatchCommand(
        string $console,
        string $phpPath,
        ?string $jobCode,
        int $jobExecutionId,
        array $batchCommandOptions
    ): array {
        $processArguments = [
            $phpPath,
            $console,
            'akeneo:batch:job',
            $jobCode,
            $jobExecutionId,
            '--quiet',
        ];

        foreach ($batchCommandOptions as $optionName => $optionValue) {
            if (in_array($optionName, ['job_execution_id', 'job_code', 'config'])) {
                continue;
            }
            switch (true) {
                case true === $optionValue:
                    $processArguments[] = sprintf('--%s', $optionName);
                    break;
                case is_scalar($optionValue) && $optionValue:
                    $processArguments[] = sprintf('--%s=%s', $optionName, $optionValue);
                    break;
                case is_array($optionValue):
                    foreach ($optionValue as $subOptionValue) {
                        $processArguments[] = sprintf('--%s=%s', $optionName, $subOptionValue);
                    }
                    break;
            }
        }

        return $processArguments;
    }

    private function executeProcess(Process $process, int $jobExecutionId): void
    {
        $this->executionManager->updateHealthCheck($jobExecutionId);

        pcntl_signal(\SIGTERM, function () use ($process) {
            $this->logger->notice('Received SIGTERM signal in watchdog command and forwarding it to subprocess');
            $process->signal(\SIGTERM);
        });

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

    private function releaseJobLock(int $jobExecutionId): void
    {
        $jobCode = $this->executionManager->jobCodeFromJobExecutionId($jobExecutionId);
        $lockIdentifier = sprintf('scheduled-job-%s', $jobCode);
        $lock = $this->lockFactory->createLock($lockIdentifier);
        if ($lock->isAcquired()) {
            $lock->release();
        }
    }
}
