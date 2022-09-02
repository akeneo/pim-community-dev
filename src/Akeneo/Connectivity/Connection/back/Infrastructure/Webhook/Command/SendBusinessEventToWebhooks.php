<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventNormalizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SendBusinessEventToWebhooks extends Command
{
    protected static $defaultName = 'akeneo:connectivity:send-business-event';
    protected static $defaultDescription = 'Send business event to webhooks';

    public function __construct(
        private BulkEventNormalizer $bulkEventNormalizer,
        private SendBusinessEventToWebhooksHandler $commandHandler,
        private EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHidden(true)
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'Symfony Messenger serialized message'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = \json_decode($input->getArgument('message'), true);
        $event = $this->bulkEventNormalizer->denormalize($message, BulkEvent::class);

        $this->commandHandler->handle(new SendBusinessEventToWebhooksCommand($event));
        $this->eventDispatcher->dispatch(new MessageProcessedEvent());

        return Command::SUCCESS;
    }
}
