<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProcessMessageCommand extends Command
{
    protected static $defaultName = 'akeneo:process-message';

    private array $handlers = [];

    public function __construct(private readonly SerializerInterface $serializer)
    {
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

//        var_export($rawEnvelope);

        $envelope = $this->serializer->decode($rawEnvelope);

//        var_dump($envelope->getMessage());

        $consumerName = $input->getArgument('consumer_name');
        $handler = $this->handlers[$consumerName] ?? null;

        if (null === $handler) {
            throw new \Exception(sprintf('No handler found for consumer %s', $consumerName));
        }

        ($handler)($envelope->getMessage());

        return Command::SUCCESS;
    }
}
