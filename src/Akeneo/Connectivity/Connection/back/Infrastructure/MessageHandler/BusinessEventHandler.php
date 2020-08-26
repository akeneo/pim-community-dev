<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\MessageHandler;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendMessageToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendMessageToWebhooksHandler;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class BusinessEventHandler implements MessageSubscriberInterface
{
    /** @var SendMessageToWebhooksHandler */
    private $sendMessageToWebhooksHandler;

    public function __construct(
        SendMessageToWebhooksHandler $sendMessageToWebhooksHandler
    ) {
        $this->sendMessageToWebhooksHandler = $sendMessageToWebhooksHandler;
    }

    public static function getHandledMessages(): iterable
    {
        yield BusinessEventInterface::class => [
            'from_transport' => 'webhook'
        ];
    }

    public function __invoke(BusinessEventInterface $event)
    {
        $this->sendMessageToWebhooksHandler->handle(new SendMessageToWebhooksCommand($event));
    }
}
