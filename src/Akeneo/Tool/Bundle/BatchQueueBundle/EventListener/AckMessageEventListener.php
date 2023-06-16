<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\EventListener;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessageInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * Using Google Pub/Sub we should ack the message within the next 10 seconds after pulling it (it can be configured
 * to 10 minutes maximum). After that the message is deliver again. As the job execution can be longer, we
 * take the decision to ack the message just after pulling it. The aim to this subscriber is to ack all job messages
 * before the job execution.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AckMessageEventListener implements EventSubscriberInterface
{
    private ContainerInterface $receiverLocator;

    public function __construct(ContainerInterface $receiverLocator)
    {
        $this->receiverLocator = $receiverLocator;
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
        if (!$envelope->getMessage() instanceof JobExecutionMessageInterface && !$envelope->getMessage() instanceof ScheduledJobMessageInterface) {
            return;
        }

        $receiver = $this->receiverLocator->get($event->getReceiverName());
        $receiver->ack($envelope);
    }
}
