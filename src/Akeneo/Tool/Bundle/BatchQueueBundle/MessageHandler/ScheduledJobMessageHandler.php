<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\MessageHandler;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\Process;

/**
 * @author    JM leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ScheduledJobMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private string $projectDir
    ) {
    }

    public function __invoke(ScheduledJobMessageInterface $scheduledJobMessage)
    {
        $console = sprintf('%s/bin/console', $this->projectDir);

        $startTime = time();
        try {
            $arguments = array_merge(
                [$console, 'akeneo:batch:watchdog', '--quiet'],
                $this->extractArgumentsFromMessage($scheduledJobMessage)
            );

            $env = [
                'SYMFONY_DOTENV_VARS' => false,
            ];
            if (null !== $scheduledJobMessage->getTenantId()) {
                $env['APP_TENANT_ID'] = $scheduledJobMessage->getTenantId();
            };

            $process = new Process($arguments, null, $env);
            $process->setTimeout(null);

            $this->logger->notice('Launching scheduled job "{code}".', [
                'code' => $scheduledJobMessage->getJobCode(),
            ]);
            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $process->run(function ($type, $buffer): void {
                \fwrite(Process::ERR === $type ? \STDERR : \STDOUT, $buffer);
            });
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()));
            $this->logger->error($t->getTraceAsString());
        }

        $executionTimeInSec = time() - $startTime;
        $this->logger->notice('Scheduled job "{code}" finished in {execution_time_in_sec} seconds.', [
            'code' => $scheduledJobMessage->getJobCode(),
            'execution_time_in_sec' => $executionTimeInSec,
        ]);
    }

    /**
     * Return all the arguments of the command to execute.
     * Options are considered as arguments.
     */
    private function extractArgumentsFromMessage(ScheduledJobMessageInterface $scheduledJobMessage): array
    {
        $arguments = [
            $scheduledJobMessage->getJobCode(),
        ];

        foreach ($scheduledJobMessage->getOptions() as $optionName => $optionValue) {
            if (true === $optionValue) {
                $arguments[] = sprintf('--%s', $optionName);
            } elseif (false !== $optionValue && null !== $optionValue) {
                $arguments[] = sprintf('--%s=%s', $optionName, $optionValue);
            }
        }

        return $arguments;
    }
}
