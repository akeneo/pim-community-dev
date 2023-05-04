<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\LimitOfEventsApiRequestsReachedLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\ReachRequestLimitLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\GetDelayUntilNextRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\Sleep;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventsApiRequestsLimitEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GetDelayUntilNextRequest $getDelayUntilNextRequest,
        private int $webhookRequestsLimit,
        private Sleep $sleep,
        private ReachRequestLimitLogger $reachRequestLimitLogger,
        private LimitOfEventsApiRequestsReachedLoggerInterface $limitOfEventsApiRequestsReachedLogger,
        private EventsApiDebugRepositoryInterface $eventsApiDebugRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageProcessedEvent::class => 'checkWebhookRequestLimit',
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
