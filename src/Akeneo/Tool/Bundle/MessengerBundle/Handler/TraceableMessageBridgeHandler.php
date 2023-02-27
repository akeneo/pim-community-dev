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
 * It extract the tenant id in order to launch the real treatment of the message
 * in a tenant aware process.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TraceableMessageBridgeHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly string $consumerName,
    ) {
    }

    public function __invoke(TraceableMessageInterface $message): void
    {
        $tenantId = $message->getTenantId();
        $correlationId = $message->getCorrelationId();

        $this->logger->info('akeneo_messenger.message_bridge.message_received', [
            'tenant_id' => $tenantId,
            'correlation_id' => $correlationId,
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

            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $startTime = time();
            $process->start();
            $process->wait();

            $this->logger->info('akeneo_messenger.message_treated', [
                'tenant_id' => $tenantId,
                'correlation_id' => $correlationId,
                'execution_time_in_sec' => time() - $startTime,
                'process_exit_code' => $process->getExitCode(),
            ]);
            $this->logger->debug('Command akeneo:process-message executed', [
                'exit_code' => $process->getExitCode(),
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput(),
            ]);
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()), [
                'trace' => $t->getTraceAsString(),
            ]);
        }
    }
}
