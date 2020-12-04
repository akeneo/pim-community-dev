<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\MessageHandler;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class BusinessEventHandler implements MessageSubscriberInterface
{
    /** @var SendBusinessEventToWebhooksHandler */
    private $commandHandler;

    public function __construct(SendBusinessEventToWebhooksHandler $commandHandler)
    {
        $this->commandHandler = $commandHandler;
    }

    public static function getHandledMessages(): iterable
    {
        yield BulkEventInterface::class => [
            'from_transport' => 'webhook'
        ];

        yield EventInterface::class => [
            'from_transport' => 'webhook',
        ];
    }

    /**
     * @param EventInterface|BulkEventInterface $event
     */
    public function __invoke(object $event)
    {
        $this->commandHandler->handle(new SendBusinessEventToWebhooksCommand($event));
    }
}
