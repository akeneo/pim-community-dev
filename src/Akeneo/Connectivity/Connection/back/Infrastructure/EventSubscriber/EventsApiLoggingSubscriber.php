<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventsApiLoggingSubscriber implements EventSubscriberInterface
{
    private EventsApiDebugRepository $eventsApiDebugRepository;

    public function __construct(EventsApiDebugRepository $eventsApiDebugRepository)
    {
        $this->eventsApiDebugRepository = $eventsApiDebugRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageProcessedEvent::class => 'flushLogs',
        ];
    }

    public function flushLogs(): void
    {
        $this->eventsApiDebugRepository->flush();
    }
}
