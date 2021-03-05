<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\LimitOfEventsApiRequestsReachedLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\ReachRequestLimitLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
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
    private ReachRequestLimitLogger $reachRequestLimitLogger;
    private LimitOfEventsApiRequestsReachedLogger $limitOfEventsApiRequestsReachedLogger;
    private EventsApiDebugRepository $eventsApiDebugRepository;

    public function __construct(
        GetDelayUntilNextRequest $getDelayUntilNextRequest,
        int $webhookRequestsLimit,
        Sleep $sleep,
        ReachRequestLimitLogger $reachRequestLimitLogger,
        LimitOfEventsApiRequestsReachedLogger $limitOfEventsApiRequestsReachedLogger,
        EventsApiDebugRepository $eventsApiDebugRepository
    ) {
        $this->getDelayUntilNextRequest = $getDelayUntilNextRequest;
        $this->webhookRequestsLimit = $webhookRequestsLimit;
        $this->sleep = $sleep;
        $this->reachRequestLimitLogger = $reachRequestLimitLogger;
        $this->limitOfEventsApiRequestsReachedLogger = $limitOfEventsApiRequestsReachedLogger;
        $this->eventsApiDebugRepository = $eventsApiDebugRepository;
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
            $this->reachRequestLimitLogger->log(
                $this->webhookRequestsLimit,
                new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
                $delayUntilNextRequest
            );

            $this->limitOfEventsApiRequestsReachedLogger->logLimitOfEventsApiRequestsReached();
            $this->eventsApiDebugRepository->flush();

            $this->sleep->sleep($delayUntilNextRequest);
        }
    }
}
