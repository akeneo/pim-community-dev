<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Handler;

use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Handler for all TraceableMessageInterface messages.
 * It extracts the tenant id in order to launch the real treatment of the message
 * in a tenant aware process.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TraceableMessageBridgeHandler implements MessageHandlerInterface
{
    private const RUNNING_PROCESS_CHECK_INTERVAL_MICROSECONDS = 1000;
    private const LONG_RUNNING_PROCESS_THRESHOLD_IN_SECONDS = 300;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly string $consumerName,
    ) {
    }

    public function __invoke(TraceableMessageInterface $message): void
    {
        $tenantId = $message->getTenantId();

        $this->logger->debug('Message received', [
            'tenant_id' => $tenantId,
            'correlation_id' => $message->getCorrelationId(),
        ]);

        $env = [
            'SYMFONY_DOTENV_VARS' => false,
        ];
        if (null !== $tenantId) {
            $env['APP_TENANT_ID'] = $tenantId;
        };

        try {
            $process = new Process([
                'php',
                'bin/console',
                'akeneo:process-message',
                $this->consumerName,
                \get_class($message),
                $this->serializer->serialize($message, 'json'),
            ], null, $env);

            $this->runProcess($process, $message);
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()), [
                'trace' => $t->getTraceAsString(),
            ]);
        }
    }

    private function runProcess(Process $process, TraceableMessageInterface $message): void
    {
        $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

        $startTime = time();
        $warningLogIsSent = false;
        $process->start();
        while ($process->isRunning()) {
            if (!$warningLogIsSent && self::LONG_RUNNING_PROCESS_THRESHOLD_IN_SECONDS <= time() - $startTime) {
                $this->logger->warning('Message handler has a long running process', [
                    'tenant_id' => $message->getTenantId(),
                    'correlation_id' => $message->getCorrelationId(),
                    'message_class' => \get_class($message),
                    'command_line' => $process->getCommandLine(),
                ]);
                $warningLogIsSent = true;
            }
            usleep(self::RUNNING_PROCESS_CHECK_INTERVAL_MICROSECONDS);
        }

        $this->logger->debug('Command akeneo:process-message executed', [
            'tenant_id' => $message->getTenantId(),
            'correlation_id' => $message->getCorrelationId(),
            'execution_time_in_sec' => time() - $startTime,
            'process_exit_code' => $process->getExitCode(),
            'process_output' => $process->getOutput(),
            'process_error_output' => $process->getErrorOutput(),
        ]);
    }
}
