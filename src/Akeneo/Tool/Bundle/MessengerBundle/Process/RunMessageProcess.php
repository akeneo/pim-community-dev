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

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(object $message, string $consumerName, ?string $tenantId, ?string $correlationId): void
    {
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

            $this->runProcess($process, $context);
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()), [
                ...$context,
                'trace' => $t->getTraceAsString(),
            ]);
        }
    }

    private function runProcess(Process $process, array $context): void
    {
        $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

        $startTime = time();
        $warningLogIsSent = false;
        $process->start();
        while ($process->isRunning()) {
            if (!$warningLogIsSent && self::LONG_RUNNING_PROCESS_THRESHOLD_IN_SECONDS <= time() - $startTime) {
                $this->logger->warning('Message handler has a long running process', [
                    ...$context,
                    'command_line' => $process->getCommandLine(),
                ]);
                $warningLogIsSent = true;
            }
            usleep(self::RUNNING_PROCESS_CHECK_INTERVAL_MICROSECONDS);
        }

        $this->logger->debug('Command akeneo:process-message executed', [
            ...$context,
            'execution_time_in_sec' => time() - $startTime,
            'process_exit_code' => $process->getExitCode(),
            'process_output' => $process->getOutput(),
            'process_error_output' => $process->getErrorOutput(),
        ]);
    }
}
