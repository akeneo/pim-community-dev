<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\MessageHandler;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Process\Process;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobMessageHandler implements MessageSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private string $projectDir,
        private FeatureFlags $featureFlags,
    ) {
    }

    public static function getHandledMessages(): iterable
    {
        yield JobExecutionMessageInterface::class => [
            'method' => 'handleJobExecution',
        ];

        yield ScheduledJobMessageInterface::class => [
            'method' => 'handleScheduledJob',
        ];
    }

    public function handleJobExecution(JobExecutionMessageInterface $jobExecutionMessage)
    {
        $this->logger->notice('Launching job watchdog for ID "{job_execution_id}".', [
            'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
        ]);

        $executionTimeInSec = $this->launchWatchdog($jobExecutionMessage);

        $this->logger->notice('Watchdog for "{job_execution_id}" finished in {execution_time_in_sec} seconds.', [
            'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            'execution_time_in_sec' => $executionTimeInSec,
        ]);
    }

    public function handleScheduledJob(ScheduledJobMessageInterface $scheduledJobMessage)
    {
        $this->logger->notice('Launching scheduled job "{code}".', [
            'code' => $scheduledJobMessage->getJobCode(),
        ]);

        $executionTimeInSec = $this->launchWatchdog($scheduledJobMessage);

        $this->logger->notice('Scheduled job "{code}" finished in {execution_time_in_sec} seconds.', [
            'code' => $scheduledJobMessage->getJobCode(),
            'execution_time_in_sec' => $executionTimeInSec,
        ]);
    }

    private function launchWatchdog(JobExecutionMessageInterface|ScheduledJobMessageInterface $jobMessage): int
    {
        $console = sprintf('%s/bin/console', $this->projectDir);

        $startTime = time();
        try {
            $arguments = array_merge(
                [$console, 'akeneo:batch:watchdog', '--quiet'],
                $this->extractArgumentsFromMessage($jobMessage)
            );

            $env = [
                'SYMFONY_DOTENV_VARS' => false,
            ];
            if (null !== $jobMessage->getTenantId()) {
                $env['APP_TENANT_ID'] = $jobMessage->getTenantId();
            };

            $process = new Process($arguments, null, $env);
            $process->setTimeout(null);

            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            if ($this->featureFlags->isEnabled('pause_jobs')) {
                $previousSigtermHandler = pcntl_signal_get_handler(\SIGTERM);

                pcntl_signal(\SIGTERM, function () use ($process, $previousSigtermHandler) {
                    $this->logger->notice('Received SIGTERM signal in job message handler and forwarding it to subprocess');
                    $process->signal(\SIGTERM);
                    if (is_callable($previousSigtermHandler)) {
                        $previousSigtermHandler();
                    }
                });
            }

            $process->run(function ($type, $buffer): void {
                \fwrite(Process::ERR === $type ? \STDERR : \STDOUT, $buffer);
            });
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()));
            $this->logger->error($t->getTraceAsString());
        }

        return $executionTimeInSec = time() - $startTime;
    }

    /**
     * Return all the arguments of the command to execute.
     * Options are considered as arguments.
     */
    private function extractArgumentsFromMessage(
        JobExecutionMessageInterface|ScheduledJobMessageInterface $jobMessage
    ): array {
        if ($jobMessage instanceof JobExecutionMessageInterface) {
            $arguments = [
                sprintf('--job_execution_id=%d', $jobMessage->getJobExecutionId()),
            ];
        } else {
            $arguments = [
                sprintf('--job_code=%s', $jobMessage->getJobCode()),
            ];
        }

        foreach ($jobMessage->getOptions() as $optionName => $optionValue) {
            switch (true) {
                case true === $optionValue:
                    $arguments[] = sprintf('--%s', $optionName);
                    break;
                case is_scalar($optionValue) && $optionValue:
                    $arguments[] = sprintf('--%s=%s', $optionName, $optionValue);
                    break;
                case is_array($optionValue):
                    foreach ($optionValue as $subOptionValue) {
                        $arguments[] = sprintf('--%s=%s', $optionName, $subOptionValue);
                    }
                    break;
            }
        }

        return $arguments;
    }
}
