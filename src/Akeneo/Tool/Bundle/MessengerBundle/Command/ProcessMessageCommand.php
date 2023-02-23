<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Command;

use Akeneo\Tool\Component\Messenger\CorrelationAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * This command should be executed by the TraceableMessageBridgeHandler. On contrary of the handler, this
 * command is tenant aware.
 * The command receives a massage and a consumer name, and based on the consumer name it executes the
 * right handler.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProcessMessageCommand extends Command
{
    protected static $defaultName = 'akeneo:process-message';

    private array $handlers = [];

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    public function registerHandler(object $handler, string $consumerName)
    {
        $this->handlers[$consumerName] = $handler;
    }

    protected function configure()
    {
        $this
            ->addArgument('message', InputArgument::REQUIRED, 'message in json')
            ->addArgument('consumer_name', InputArgument::REQUIRED, 'Consumer name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rawEnvelope = \json_decode($input->getArgument('message'), true, 512, JSON_THROW_ON_ERROR);

        $envelope = $this->serializer->decode($rawEnvelope);
        $message = $envelope->getMessage();

        $consumerName = $input->getArgument('consumer_name');
        $handler = $this->handlers[$consumerName] ?? null;

        if (null === $handler) {
            throw new \Exception(sprintf('No handler found for the "%s" consumer', $consumerName));
        }

        try {
            ($handler)($message);
        } catch (\Throwable $t) {
            $context = ['trace' => $t->getTraceAsString()];
            if ($message instanceof CorrelationAwareInterface) {
                $context['correlation_id'] = $message->getCorrelationId();
            }

            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()), $context);

            throw $t;
        }

        return Command::SUCCESS;
    }
}
