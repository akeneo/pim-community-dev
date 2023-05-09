<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventSubscriptionSkippedOwnEventLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookUserAuthenticator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClientInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQueryInterface;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Generator;
use Psr\Log\LoggerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksHandler
{
    public function __construct(
        private SelectActiveWebhooksQueryInterface $selectActiveWebhooksQuery,
        private WebhookUserAuthenticator $webhookUserAuthenticator,
        private WebhookClientInterface $client,
        private WebhookEventBuilder $builder,
        private LoggerInterface $logger,
        private EventSubscriptionSkippedOwnEventLoggerInterface $eventSubscriptionSkippedOwnEventLogger,
        private string $pimSource
    ) {
    }

    public function handle(SendBusinessEventToWebhooksCommand $command): void
    {
        $webhooks = $this->selectActiveWebhooksQuery->execute();

        if (0 === \count($webhooks)) {
            return;
        }

        $pimEventBulk = $command->event();

        $requests = function () use ($pimEventBulk, $webhooks): Generator {
            foreach ($webhooks as $webhook) {
                $user = $this->webhookUserAuthenticator->authenticate($webhook->userId());

                $filteredPimEventBulk = $this->filterConnectionOwnEvents(
                    $webhook,
                    $user->getUserIdentifier(),
                    $pimEventBulk
                );
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
                            'is_using_uuid' => $webhook->isUsingUuid(),
                        ]
                    );

                    if (0 === \count($apiEvents)) {
                        continue;
                    }

                    yield new WebhookRequest(
                        $webhook,
                        $apiEvents
                    );
                } catch (WebhookEventDataBuilderNotFoundException $dataBuilderNotFoundException) {
                    $this->logger->warning($dataBuilderNotFoundException->getMessage());
                }
            }
        };

        $this->client->bulkSend($requests());
    }

    private function filterConnectionOwnEvents(
        ActiveWebhook $webhook,
        string $username,
        BulkEventInterface $bulkEvent
    ): ?BulkEventInterface {
        $events = \array_filter(
            $bulkEvent->getEvents(),
            function (EventInterface $event) use ($username, $webhook): bool {
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

        if (\count($events) === 0) {
            return null;
        }

        return new BulkEvent($events);
    }
}
