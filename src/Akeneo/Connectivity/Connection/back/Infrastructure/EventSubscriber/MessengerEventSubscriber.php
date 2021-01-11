<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionRequestsLimitReachedLog;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\GetDelayUntilNextRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\Sleep;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerEventSubscriber implements EventSubscriberInterface
{
    private GetDelayUntilNextRequest $getDelayUntilNextRequest;
    private int $webhookRequestsLimit;
    private Sleep $sleep;
    private LoggerInterface $logger;

    public function __construct(
        GetDelayUntilNextRequest $getDelayUntilNextRequest,
        int $webhookRequestsLimit,
        Sleep $sleep,
        LoggerInterface $logger
    ) {
        $this->getDelayUntilNextRequest = $getDelayUntilNextRequest;
        $this->webhookRequestsLimit = $webhookRequestsLimit;
        $this->sleep = $sleep;
        $this->logger = $logger;
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

        if (0 <= $delayUntilNextRequest) {
            $this->logger->info(
                json_encode(
                    (EventSubscriptionRequestsLimitReachedLog::fromLimit(
                        $this->webhookRequestsLimit
                    ))->toLog(),
                    JSON_THROW_ON_ERROR
                )
            );

            $this->sleep->execute($delayUntilNextRequest);
        }
    }
}
