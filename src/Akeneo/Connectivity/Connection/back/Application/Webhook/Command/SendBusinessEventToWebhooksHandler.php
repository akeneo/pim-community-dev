<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionEventBuildLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSkipOwnEventLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookUserAuthenticator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetConnectionUserForFakeSubscription;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiRequestCountRepository;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Psr\Log\LoggerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SendBusinessEventToWebhooksHandler
{
    const FAKE_CONNECTION_CODE = 'FAKE_CONNECTION_CODE';
    const FAKE_SECRET = 'FAKE_SECRET';
    const FAKE_URL = 'FAKE_URL';
    const NUMBER_FAKE_WEBHOOKS = 3;

    private SelectActiveWebhooksQuery $selectActiveWebhooksQuery;
    private WebhookUserAuthenticator $webhookUserAuthenticator;
    private WebhookClient $client;
    private WebhookEventBuilder $builder;
    private LoggerInterface $logger;
    private GetConnectionUserForFakeSubscription $connectionUserForFakeSubscription;
    private EventsApiRequestCountRepository $eventsApiRequestRepository;
    private CacheClearerInterface $cacheClearer;
    private string $pimSource;
    private ?\Closure $getTimeCallable;

    public function __construct(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        WebhookUserAuthenticator $webhookUserAuthenticator,
        WebhookClient $client,
        WebhookEventBuilder $builder,
        LoggerInterface $logger,
        GetConnectionUserForFakeSubscription $connectionUserForFakeSubscription,
        EventsApiRequestCountRepository $eventsApiRequestRepository,
        CacheClearerInterface $cacheClearer,
        string $pimSource,
        ?callable $getTimeCallable = null
    ) {
        $this->selectActiveWebhooksQuery = $selectActiveWebhooksQuery;
        $this->webhookUserAuthenticator = $webhookUserAuthenticator;
        $this->client = $client;
        $this->builder = $builder;
        $this->logger = $logger;
        $this->connectionUserForFakeSubscription = $connectionUserForFakeSubscription;
        $this->eventsApiRequestRepository = $eventsApiRequestRepository;
        $this->cacheClearer = $cacheClearer;
        $this->pimSource = $pimSource;
        $this->getTimeCallable = null !== $getTimeCallable ? \Closure::fromCallable($getTimeCallable) : null;
    }

    public function handle(SendBusinessEventToWebhooksCommand $command): void
    {
        $webhooks = $this->selectActiveWebhooksQuery->execute();
        $isFake = false;

        if (0 === count($webhooks)) {
            $userId = $this->connectionUserForFakeSubscription->execute();

            if (null === $userId) {
                return;
            }

            $webhooks = $this->buildFakeActiveWebhooks($userId);
            $isFake = true;
        }

        $event = $command->event();

        $requests = function () use ($event, $webhooks) {
            $apiEventsRequestCount = 0;
            $cumulatedTimeMs = 0;
            $eventBuiltCount = 0;
            $startTime = $this->getTime();

            foreach ($webhooks as $webhook) {
                $user = $this->webhookUserAuthenticator->authenticate($webhook->userId());

                // TODO CXP-604 temporarly deactivated
                // $filteredEvent = $this->filterConnectionOwnEvents($webhook, $user->getUsername(), $event);
                // if (null === $filteredEvent) {
                //     continue;
                // }

                try {
                    $webhookEvents = $this->builder->build(
                        $event,
                        [
                            'user' => $user,
                            'pim_source' => $this->pimSource,
                            'connection_code' => $webhook->connectionCode(),
                        ]
                    );

                    if (0 === count($webhookEvents)) {
                        continue;
                    }

                    $cumulatedTimeMs += $this->getTime() - $startTime;
                    $eventBuiltCount++;

                    yield new WebhookRequest(
                        $webhook,
                        $webhookEvents
                    );

                    $apiEventsRequestCount++;

                    $startTime = $this->getTime();
                } catch (WebhookEventDataBuilderNotFoundException $dataBuilderNotFoundException) {
                    $this->logger->warning($dataBuilderNotFoundException->getMessage());
                }
            }

            $this->eventsApiRequestRepository
                ->upsert(new \DateTimeImmutable('now', new \DateTimeZone('UTC')), $apiEventsRequestCount);

            if ($eventBuiltCount > 0) {
                $this->logger->info(
                    json_encode(
                        (new EventSubscriptionEventBuildLog(
                            count($webhooks),
                            $event,
                            $cumulatedTimeMs,
                            $eventBuiltCount
                        ))->toLog(),
                        JSON_THROW_ON_ERROR
                    )
                );
            }
        };

        if ($isFake) {
            $this->client->bulkFakeSend($requests());
        } else {
            $this->client->bulkSend($requests());
        }

        $this->cacheClearer->clear();
    }

    /**
     * @param EventInterface|BulkEventInterface $event
     *
     * @return EventInterface|BulkEventInterface|null
     */
    private function filterConnectionOwnEvents(ActiveWebhook $webhook, string $username, object $event): ?object
    {
        if ($event instanceof BulkEventInterface) {
            $events = array_filter(
                $event->getEvents(),
                function (EventInterface $event) use ($username, $webhook) {
                    if ($username === $event->getAuthor()->name()) {
                        $this->logger->info(
                            json_encode(
                                (EventSubscriptionSkipOwnEventLog::fromEvent(
                                    $event,
                                    $webhook->connectionCode()
                                ))->toLog(),
                                JSON_THROW_ON_ERROR
                            )
                        );

                        return false;
                    }

                    return true;
                }
            );
            if (count($events) === 0) {
                return null;
            }

            return new BulkEvent($events);
        }

        if ($event instanceof EventInterface && $username === $event->getAuthor()->name()) {
            $this->logger->info(
                json_encode(
                    (EventSubscriptionSkipOwnEventLog::fromEvent($event, $webhook->connectionCode()))->toLog(),
                    JSON_THROW_ON_ERROR
                )
            );

            return null;
        }

        return $event;
    }

    /**
     * Get the current time in milliseconds.
     */
    private function getTime(): int
    {
        if (null !== $this->getTimeCallable) {
            return call_user_func($this->getTimeCallable);
        }

        return (int)round(microtime(true) * 1000);
    }

    /**
     * @return array<ActiveWebhook>
     */
    private function buildFakeActiveWebhooks(int $userId): array
    {
        return array_fill(
            0,
            self::NUMBER_FAKE_WEBHOOKS,
            new ActiveWebhook(
                self::FAKE_CONNECTION_CODE,
                $userId,
                self::FAKE_SECRET,
                self::FAKE_URL
            )
        );
    }
}
