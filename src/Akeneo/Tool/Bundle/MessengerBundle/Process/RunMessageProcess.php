<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Process;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RunMessageProcess
{
    private const RUNNING_PROCESS_CHECK_INTERVAL_MICROSECONDS = 1000;
    private const LONG_RUNNING_PROCESS_THRESHOLD_IN_SECONDS = 300;
    private const PROCESS_TIME_LIMIT_IN_SECONDS = 3600; // 60 min
    private const MODIFY_ACK_DEADLINE_FREQUENCY_IN_SECONDS = 480; // 8 min

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        object $message,
        string $consumerName,
        ?string $tenantId,
        ?string $correlationId,
        callable $modifyAckDeadlineFn
    ): void {
        $context = [
            'tenant_id' => $tenantId,
            'correlation_id' => $correlationId,
            'consumer_name' => $consumerName,
            'message_class' => \get_class($message),
        ];

        $this->logger->debug('Message received', $context);

        $env = [
            'SYMFONY_DOTENV_VARS' => false,
        ];

        if (null !== $tenantId && '' !== $tenantId) {
            $env['APP_TENANT_ID'] = $tenantId;
        }

        $startTime = time();
        try {
            $process = new Process([
                'php',
                'bin/console',
                'akeneo:process-message',
                $consumerName,
                \get_class($message),
                $this->serializer->serialize($message, 'json'),
                $correlationId,
            ], null, $env);

            $exitCode = $this->runProcess($process, $modifyAckDeadlineFn, $context);
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()), [
                ...$context,
                'trace' => $t->getTraceAsString(),
            ]);

            throw $t;
        }

        $this->logger->info('Message is handled', \array_merge($context, [
            'duration_time_in_secs' => time() - $startTime,
        ]));

        if (0 !== $exitCode) {
            throw new \RuntimeException(\sprintf('An error occurred, exit code: %d', $exitCode));
        }
    }

    private function runProcess(Process $process, callable $modifyAckDeadlineFn, array $context): ?int
    {
        $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

        $startTime = time();
        $modifyAckDeadlineTime = $startTime;
        $warningLogIsSent = false;
        $process->start(function ($type, $buffer): void {
            \fwrite(Process::ERR === $type ? \STDERR : \STDOUT, $buffer);
        });
        while ($process->isRunning()) {
            if (!$warningLogIsSent && self::LONG_RUNNING_PROCESS_THRESHOLD_IN_SECONDS <= time() - $startTime) {
                $this->logger->warning('Message handler has a long running process', [
                    ...$context,
                    'command_line' => $process->getCommandLine(),
                ]);
                $warningLogIsSent = true;
            }

            // PubSub has a limit to ack the message, otherwise it's available again for another consumer.
            // We can modify the ack deadline to indicate we need more time.
            // See https://cloud.google.com/pubsub/docs/reference/rest/v1/projects.subscriptions/modifyAckDeadline
            if (self::MODIFY_ACK_DEADLINE_FREQUENCY_IN_SECONDS <= time() - $modifyAckDeadlineTime) {
                $modifyAckDeadlineFn();
                $modifyAckDeadlineTime = time();
            }

            if (self::PROCESS_TIME_LIMIT_IN_SECONDS <= time() - $startTime) {
                $process->stop();

                throw new \RuntimeException('Process time limit exceeded');
            }

            usleep(self::RUNNING_PROCESS_CHECK_INTERVAL_MICROSECONDS);
        }

        if (0 !== $process->getExitCode()) {
            $this->logger->error('Process has no success error code', [
                ...$context,
                'execution_time_in_sec' => time() - $startTime,
                'process_exit_code' => $process->getExitCode(),
                'process_output' => $process->getOutput(),
                'process_error_output' => $process->getErrorOutput(),
            ]);
        } else {
            $this->logger->info('Command akeneo:process-message executed', [
                ...$context,
                'execution_time_in_sec' => time() - $startTime,
                'process_exit_code' => $process->getExitCode(),
                'process_output' => $process->getOutput(),
                'process_error_output' => $process->getErrorOutput(),
            ]);
        }

        return $process->getExitCode();
    }
}
