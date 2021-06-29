<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\MessageHandler;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class BusinessEventHandler implements MessageSubscriberInterface
{
    private SendBusinessEventToWebhooksHandler $commandHandler;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        SendBusinessEventToWebhooksHandler $commandHandler,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->commandHandler = $commandHandler;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getHandledMessages(): iterable
    {
        yield BulkEventInterface::class => [
            'from_transport' => 'webhook'
        ];
    }

    public function __invoke(BulkEventInterface $event): void
    {
        $this->commandHandler->handle(new SendBusinessEventToWebhooksCommand($event));
        $this->eventDispatcher->dispatch(new MessageProcessedEvent());
    }
}
