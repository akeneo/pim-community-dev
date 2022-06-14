<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\MessageHandler;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Command\SendBusinessEventToWebhooks;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\BulkEventNormalizer;
use Akeneo\Tool\Bundle\MessengerBundle\Serialization\JsonSerializer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Process\Process;

class BusinessEventHandler implements MessageSubscriberInterface
{
    public function __construct(
        private string $projectDir,
        private LoggerInterface $logger,
        private BulkEventNormalizer $normalizer
    ) {
    }

    public static function getHandledMessages(): iterable
    {
        yield BulkEventInterface::class => [
            'from_transport' => 'webhook',
        ];
    }

    public function __invoke(BulkEventInterface $event): void
    {
        try {
            $processArguments = $this->buildBatchCommand($event);

            $env = [
                'SYMFONY_DOTENV_VARS' => false,
            ];

            $process = new Process($processArguments, null, $env);
            $process->setTimeout(null);

            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $process->run(function ($type, $buffer): void {
                \fwrite(Process::ERR === $type ? \STDERR : \STDOUT, $buffer);
            });
        } catch (\Throwable $t) {
            $this->logger->error(
                sprintf('An error occurred: %s', $t->getMessage()),
                ['exception' => $t]
            );
        }
    }

    private function buildBatchCommand(BulkEventInterface $event): array
    {
        $message = $this->normalizer->normalize($event);
        $processArguments = [
            sprintf('%s/bin/console', $this->projectDir),
            '--quiet',
            SendBusinessEventToWebhooks::getDefaultName(),
            $message,
        ];

        return $processArguments;
    }

}
