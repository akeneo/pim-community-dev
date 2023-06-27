<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\EventListener;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Webmozart\Assert\Assert;

/**
 * Using Google Pub/Sub we should ack the message within the next 10 seconds after pulling it (it can be configured
 * to 10 minutes maximum). After that the message is delivered again. As the job execution can be longer, we
 * take the decision to ack the message just after pulling it. The aim to this subscriber is to ack all job messages
 * before the job execution.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AckMessageEventListener implements EventSubscriberInterface
{
    public function __construct(
        private ContainerInterface $receiverLocator,
        private string $receiverName
    ) {
        Assert::stringNotEmpty($this->receiverName);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'ackMessage',
        ];
    }

    public function ackMessage(WorkerMessageReceivedEvent $event): void
    {
        $envelope = $event->getEnvelope();

        $receiverName = $event->getReceiverName();
        if ($receiverName === $this->receiverName) {
            $receiver = $this->receiverLocator->get($receiverName);
            $receiver->ack($envelope);
        }
    }
}
