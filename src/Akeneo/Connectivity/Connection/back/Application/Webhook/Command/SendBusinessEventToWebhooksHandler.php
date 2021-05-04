<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventSubscriptionSkippedOwnEventLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\EventDataVersionLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookUserAuthenticator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
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
    private SelectActiveWebhooksQuery $selectActiveWebhooksQuery;
    private WebhookUserAuthenticator $webhookUserAuthenticator;
    private WebhookClient $client;
    private WebhookEventBuilder $builder;
    private LoggerInterface $logger;
    private EventSubscriptionSkippedOwnEventLogger $eventSubscriptionSkippedOwnEventLogger;
    private EventDataVersionLogger $eventDataVersionLogger;
    private EventsApiDebugRepository $eventsApiDebugRepository;
    private EventsApiRequestCountRepository $eventsApiRequestRepository;
    private CacheClearerInterface $cacheClearer;
    private string $pimSource;

    public function __construct(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        WebhookUserAuthenticator $webhookUserAuthenticator,
        WebhookClient $client,
        WebhookEventBuilder $builder,
        LoggerInterface $logger,
        EventSubscriptionSkippedOwnEventLogger $eventSubscriptionSkippedOwnEventLogger,
        EventDataVersionLogger $eventDataVersionLogger,
        EventsApiDebugRepository $eventsApiDebugRepository,
        EventsApiRequestCountRepository $eventsApiRequestRepository,
        CacheClearerInterface $cacheClearer,
        string $pimSource
    ) {
        $this->selectActiveWebhooksQuery = $selectActiveWebhooksQuery;
        $this->webhookUserAuthenticator = $webhookUserAuthenticator;
        $this->client = $client;
        $this->builder = $builder;
        $this->logger = $logger;
        $this->eventSubscriptionSkippedOwnEventLogger = $eventSubscriptionSkippedOwnEventLogger;
        $this->eventDataVersionLogger = $eventDataVersionLogger;
        $this->eventsApiDebugRepository = $eventsApiDebugRepository;
        $this->eventsApiRequestRepository = $eventsApiRequestRepository;
        $this->cacheClearer = $cacheClearer;
        $this->pimSource = $pimSource;
    }

    public function handle(SendBusinessEventToWebhooksCommand $command): void
    {
        $webhooks = $this->selectActiveWebhooksQuery->execute();

        if (0 === count($webhooks)) {
            return;
        }

        $pimEventBulk = $command->event();

        $requests = function () use ($pimEventBulk, $webhooks) {
            $apiEventsRequestCount = 0;
            $versions = [];

            foreach ($webhooks as $webhook) {
                $user = $this->webhookUserAuthenticator->authenticate($webhook->userId());

                $filteredPimEventBulk = $this->filterConnectionOwnEvents($webhook, $user->getUsername(), $pimEventBulk);
                if (null === $filteredPimEventBulk) {
                    continue;
                }

                try {
                    $apiEvents = $this->builder->build(
                        $filteredPimEventBulk,
                        [
                            'user' => $user,
                            'pim_source' => $this->pimSource,
                            'connection_code' => $webhook->connectionCode(),
                        ]
                    );

                    foreach ($apiEvents as $apiEvent) {
                        if (null !== $apiEvent->version()) {
                            $versions[$apiEvent->getPimEvent()->getUuid()] = $apiEvent->version();
                        }
                    }

                    if (0 === count($apiEvents)) {
                        continue;
                    }

                    yield new WebhookRequest(
                        $webhook,
                        $apiEvents
                    );

                    $apiEventsRequestCount++;
                } catch (WebhookEventDataBuilderNotFoundException $dataBuilderNotFoundException) {
                    $this->logger->warning($dataBuilderNotFoundException->getMessage());
                }
            }

            foreach ($versions as $version) {
                $this->eventDataVersionLogger->log($version);
            }

            $this->eventsApiRequestRepository
                ->upsert(new \DateTimeImmutable('now', new \DateTimeZone('UTC')), $apiEventsRequestCount);
        };

        $this->client->bulkSend($requests());

        $this->cacheClearer->clear();
        $this->eventsApiDebugRepository->flush();
    }

    private function filterConnectionOwnEvents(
        ActiveWebhook $webhook,
        string $username,
        BulkEventInterface $bulkEvent
    ): ?BulkEventInterface {
        $events = array_filter(
            $bulkEvent->getEvents(),
            function (EventInterface $event) use ($username, $webhook) {
                if ($username === $event->getAuthor()->name()) {
                    $this->eventSubscriptionSkippedOwnEventLogger
                        ->logEventSubscriptionSkippedOwnEvent(
                            $webhook->connectionCode(),
                            $event
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
}
