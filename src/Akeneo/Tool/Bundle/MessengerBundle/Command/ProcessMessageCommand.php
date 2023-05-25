<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Command;

use Akeneo\Tool\Bundle\MessengerBundle\Registry\UcsMessageHandlerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * This command should be executed by the TraceableMessageBridgeHandler. On contrary of the handler, this
 * command is tenant aware.
 * The command receives a message and a consumer name, and based on the consumer name it executes the
 * right handler.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProcessMessageCommand extends Command
{
    protected static $defaultName = 'akeneo:process-message';

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly UcsMessageHandlerRegistry $ucsMessageHandlerRegistry,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('consumer_name', InputArgument::REQUIRED, 'consumer name')
            ->addArgument('message_class', InputArgument::REQUIRED, 'class of the message')
            ->addArgument('message', InputArgument::REQUIRED, 'message in json')
            ->addArgument('correlation_id', InputArgument::OPTIONAL, 'Correlation ID of the message')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $this->serializer->deserialize(
            $input->getArgument('message'),
            $input->getArgument('message_class'),
            'json'
        );

        $consumerName = $input->getArgument('consumer_name');
        $handler = $this->ucsMessageHandlerRegistry->getHandler($consumerName);

        try {
            ($handler)($message);
        } catch (\Throwable $t) {
            $context = [
                'trace' => $t->getTraceAsString(),
                'message_class' => $input->getArgument('message_class'),
                'correlation_id' => $input->getArgument('correlation_id')
            ];

            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()), $context);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
