<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\MessageHandler;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private string $projectDir
    ) {
    }

    public function __invoke(JobExecutionMessageInterface $jobExecutionMessage)
    {
        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        $startTime = time();
        try {
            $arguments = array_merge(
                [$pathFinder->find(), $console, 'akeneo:batch:watchdog', '--quiet'],
                $this->extractArgumentsFromMessage($jobExecutionMessage)
            );

            $env = null !== $jobExecutionMessage->tenantId() ? [
                'APP_TENANT_ID' => $jobExecutionMessage->tenantId(),
            ] : null;

            $process = new Process($arguments, null, $env);
            $process->setTimeout(null);

            $this->logger->notice('Launching job watchdog for ID "{job_execution_id}".', [
                'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            ]);
            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $process->run(function ($type, $buffer) {
                $level = Process::ERR === $type ? 'error' : 'info';
                $this->logger->$level($buffer);
            });
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()));
            $this->logger->error($t->getTraceAsString());
        }

        $executionTimeInSec = time() - $startTime;
        $this->logger->notice('Watchdog for "{job_execution_id}" finished in {execution_time_in_sec} seconds.', [
            'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            'execution_time_in_sec' => $executionTimeInSec,
        ]);
    }

    /**
     * Return all the arguments of the command to execute.
     * Options are considered as arguments.
     */
    private function extractArgumentsFromMessage(JobExecutionMessageInterface $jobExecutionMessage): array
    {
        $arguments = [
            $jobExecutionMessage->getJobExecutionId(),
        ];

        foreach ($jobExecutionMessage->getOptions() as $optionName => $optionValue) {
            if (true === $optionValue) {
                $arguments[] = sprintf('--%s', $optionName);
            } elseif (false !== $optionValue && null !== $optionValue) {
                $arguments[] = sprintf('--%s=%s', $optionName, $optionValue);
            }
        }

        return $arguments;
    }
}
