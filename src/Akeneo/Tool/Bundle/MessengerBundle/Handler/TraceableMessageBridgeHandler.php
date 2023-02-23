<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Handler;

use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Process\Process;

/**
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

    public function __invoke(TraceableMessageInterface $message)
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

        $process = new Process([
            'php',
            'bin/console',
            'akeneo:process-message',
            \json_encode($this->serializer->encode(new Envelope($message))),
            $this->consumerName,
        ], null, $env);

        $process->start();
        $process->wait();

        $this->logger->info('akeneo_messenger.message_treated', [
            'tenant_id' => $tenantId,
            'correlation_id' => $correlationId,
        ]);

        $output = $process->getOutput();
        $exitCode = $process->getExitCode();
        echo "Output = " . $output . ", exitCode = $exitCode" . PHP_EOL;
    }
}
