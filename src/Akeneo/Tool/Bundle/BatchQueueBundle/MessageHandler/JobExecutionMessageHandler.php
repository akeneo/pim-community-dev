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
        $console = sprintf('%s/bin/console', $this->projectDir);

        $startTime = time();
        try {
            $arguments = array_merge(
                [(new PhpExecutableFinder())->find(), $console, 'akeneo:batch:watchdog', '--quiet'],
                $this->extractArgumentsFromMessage($jobExecutionMessage)
            );

            $env = [
                'SYMFONY_DOTENV_VARS' => false,
            ];
            if (null !== $jobExecutionMessage->getTenantId()) {
                $env['APP_TENANT_ID'] = $jobExecutionMessage->getTenantId();
            };

            $process = new Process($arguments, null, $env);
            $process->setTimeout(null);

            $this->logger->notice('Launching job watchdog for ID "{job_execution_id}".', [
                'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
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
