<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure;

use Akeneo\Pim\Platform\Messaging\Domain\MessageTenantAwareInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Process\Process;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly string $consumerName,
    ) {
    }

    public function __invoke(MessageTenantAwareInterface $message)
    {
        $tenantId = $message->getTenantId();

        print_r("tenantId = $tenantId\n");

        $env = [
            'SYMFONY_DOTENV_VARS' => false,
        ];
        if (null !== $message->getTenantId()) {
            $env['APP_TENANT_ID'] = $message->getTenantId();
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

        $output = $process->getOutput();
        $exitCode = $process->getExitCode();
        echo "Output = " . $output . ", exitCode = $exitCode" . PHP_EOL;
    }
}
