<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Handler;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\CorrelationIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Handler for all messages in UCS infra.
 * It extracts the tenant id and the consumer name to launch the real treatment of the message in a tenant aware process.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UcsEnvelopeMessageHandler
{
    private const RUNNING_PROCESS_CHECK_INTERVAL_MICROSECONDS = 1000;
    private const LONG_RUNNING_PROCESS_THRESHOLD_IN_SECONDS = 300;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Envelope $envelope): void
    {
        $message = $envelope->getMessage();
        $correlationId = $envelope->last(CorrelationIdStamp::class)?->correlationId();

        $tenantId = $envelope->last(TenantIdStamp::class)?->pimTenantId();
        if (null === $tenantId) {
            throw new \LogicException('The envelope must have a tenant ID');
        }

        $consumerName = $envelope->last(ReceivedStamp::class)?->getTransportName();
        if (null === $consumerName) {
            throw new \LogicException('The envelope must have a consumer name from a ReceivedStamp');
        }

        $context = [
            'tenant_id' => $tenantId,
            'correlation_id' => $correlationId,
            'consumer_name' => $consumerName,
            'message_class' => \get_class($message),
        ];

        $this->logger->debug('Message received', $context);

        $env = [
            'SYMFONY_DOTENV_VARS' => false,
            'APP_TENANT_ID' => $tenantId,
        ];

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
