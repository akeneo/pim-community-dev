<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Connector\Step\LockedTasklet;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Process\Process;

/**
 * The watchdog process is launched by the daemon consuming messages to run jobs. This watchdog process  launches
 * itself a child process to run a single Akeneo job. The daemon does not run directly the jobs:
 * - the daemon is tenant agnostic whereas watchdog process is dedicated for a tenant
 * - if the job die for unexpected reason, the job status is updated by the watchdog, which is possible as it can
 * access to the database (tenant specific)
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ScheduledJobWatchdogCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:scheduled';
    protected static $defaultDescription = '[Internal] Launched by the job queue consumer';

    /** Interval in seconds before updating health check if job is still running. */
    public const HEALTH_CHECK_INTERVAL = 5;

    /** Interval in microseconds before checking if the process is still running. */
    private const RUNNING_PROCESS_CHECK_INTERVAL = 200000;

    public function __construct(
        private JobExecutionManager $executionManager,
        private LoggerInterface $logger,
        protected LockFactory $lockFactory,
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHidden(true)
            ->addArgument('batch_code', InputArgument::REQUIRED, 'Scheduled job code')
            ->addOption(
                'no-log',
                null,
                InputOption::VALUE_NONE,
                "Don't display logs"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $console = sprintf('%s/bin/console', $this->projectDir);
        $startTime = time();
        $scheduledJobCode = $input->getArgument('batch_code');
        try {
            $processArguments = $this->buildBatchCommand(
                $console,
                $scheduledJobCode,
                $input->getOptions()
            );
            $process = new Process($processArguments);
            $process->setTimeout(null);

            $this->logger->notice('Launching job execution "{code}".', [
                'code' => $scheduledJobCode,
            ]);
            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $this->executeProcess($process, $scheduledJobCode);
        } catch (\Throwable $t) {
            $this->logger->error(
                sprintf('An error occurred: %s', $t->getMessage()),
                ['exception' => $t]
            );
        } finally {
            // update status if the job execution failed due to an uncatchable error as a fatal error
            if ($this->executionManager->getExitStatus((int)$scheduledJobCode)?->isRunning()) {
                $this->executionManager->markAsFailed($scheduledJobCode);
                $lockIdentifier = LockedTasklet::getLockIdentifier($scheduledJobCode);
                $lock = $this->lockFactory->createLock($lockIdentifier);
                $lock->release();
            }
        }

        $executionTimeInSec = time() - $startTime;
        $this->logger->notice('Job execution "{code}" is finished in {execution_time_in_sec} seconds.', [
            'code' => $scheduledJobCode,
            'execution_time_in_sec' => $executionTimeInSec,
        ]);

        return Command::SUCCESS;
    }

    private function buildBatchCommand(
        string $console,
        string $scheduledJobCode,
        array $watchdogOptions
    ): array {
        $processArguments = [
            'php',
            $console,
            'akeneo:batch:job',
            $scheduledJobCode,
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

    private function executeProcess(Process $process, string $scheduledJobCode): void
    {
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
