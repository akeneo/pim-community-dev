<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\MessageHandler;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
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
        yield BusinessEventInterface::class => [
            'from_transport' => 'webhook'
        ];
    }

    public function __invoke(BusinessEventInterface $businessEvent)
    {
        $this->commandHandler->handle(new SendBusinessEventToWebhooksCommand($businessEvent));
    }
}
