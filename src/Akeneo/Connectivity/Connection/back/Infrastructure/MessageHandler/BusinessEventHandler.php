<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\MessageHandler;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
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
    }

    public function __invoke(BulkEventInterface $event)
    {
        $this->commandHandler->handle(new SendBusinessEventToWebhooksCommand($event));
    }
}
