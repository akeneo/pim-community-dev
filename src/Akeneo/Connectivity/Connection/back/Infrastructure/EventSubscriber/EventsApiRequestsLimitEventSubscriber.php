<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionRequestsLimitReachedLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventSubscriptionLogInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiDebugLogger;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\GetDelayUntilNextRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\Sleep;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventsApiRequestsLimitEventSubscriber implements EventSubscriberInterface
{
    private GetDelayUntilNextRequest $getDelayUntilNextRequest;
    private int $webhookRequestsLimit;
    private Sleep $sleep;
    private EventSubscriptionLogInterface $eventSubscriptionLog;
    private EventsApiDebugLogger $eventsApiDebugLogger;

    public function __construct(
        GetDelayUntilNextRequest $getDelayUntilNextRequest,
        int $webhookRequestsLimit,
        Sleep $sleep,
        EventSubscriptionLogInterface $eventSubscriptionLog,
        EventsApiDebugLogger $eventsApiDebugLogger
    ) {
        $this->getDelayUntilNextRequest = $getDelayUntilNextRequest;
        $this->webhookRequestsLimit = $webhookRequestsLimit;
        $this->sleep = $sleep;
        $this->eventSubscriptionLog = $eventSubscriptionLog;
        $this->eventsApiDebugLogger = $eventsApiDebugLogger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerRunningEvent::class => 'checkWebhookRequestLimit',
        ];
    }

    public function checkWebhookRequestLimit(): void
    {
        $delayUntilNextRequest = $this->getDelayUntilNextRequest->execute(
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            $this->webhookRequestsLimit
        );

        if ($delayUntilNextRequest > 0) {
            $this->eventSubscriptionLog->logReachRequestLimit(
                $this->webhookRequestsLimit,
                new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
                $delayUntilNextRequest
            );

            $this->eventsApiDebugLogger->logLimitOfEventApiRequestsReached();
            $this->eventsApiDebugLogger->flushLogs();

            $this->sleep->sleep($delayUntilNextRequest);
        }
    }
}
