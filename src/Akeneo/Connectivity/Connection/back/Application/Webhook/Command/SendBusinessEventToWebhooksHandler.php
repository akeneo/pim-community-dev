<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\WebhookEventBuildLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Log\WebhookEventDataBuilderErrorLog;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookUserAuthenticator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetConnectionUserForFakeSubscription;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
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

    private SelectActiveWebhooksQuery $selectActiveWebhooksQuery;
    private WebhookUserAuthenticator $webhookUserAuthenticator;
    private WebhookClient $client;
    private WebhookEventBuilder $builder;
    private LoggerInterface $logger;
    private GetConnectionUserForFakeSubscription $connectionUserForFakeSubscription;
    private string $pimSource;

    public function __construct(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        WebhookUserAuthenticator $webhookUserAuthenticator,
        WebhookClient $client,
        WebhookEventBuilder $builder,
        LoggerInterface $logger,
        GetConnectionUserForFakeSubscription $connectionUserForFakeSubscription,
        string $pimSource
    ) {
        $this->selectActiveWebhooksQuery = $selectActiveWebhooksQuery;
        $this->webhookUserAuthenticator = $webhookUserAuthenticator;
        $this->client = $client;
        $this->builder = $builder;
        $this->logger = $logger;
        $this->connectionUserForFakeSubscription = $connectionUserForFakeSubscription;
        $this->pimSource = $pimSource;
    }

    public function handle(SendBusinessEventToWebhooksCommand $command): void
    {
        $startTime = microtime(true);

        $webhooks = $this->selectActiveWebhooksQuery->execute();
        $isFake = false;

        if (0 === count($webhooks)) {
            $userId = $this->connectionUserForFakeSubscription->execute();

            if (null === $userId) {
                return;
            }

            $webhooks[] = $this->buildFakeActiveWebhook($userId);
            $isFake = true;
        }

        $businessEvent = $command->businessEvent();

        $requests = function () use ($businessEvent, $webhooks) {
            foreach ($webhooks as $webhook) {
                try {
                    $this->webhookUserAuthenticator->authenticate($webhook->userId());
                    $event = $this->builder->build($businessEvent, ['pim_source' => $this->pimSource]);
                } catch (\Throwable $error) {
                    // Handle error gracefully and continue the processing of other webhooks.
                    $this->handleError($error, $webhook, $businessEvent);
                    continue;
                }

                yield new WebhookRequest($webhook, $event);
            }
        };

        $endTimeBeforeSend = microtime(true);

        $webhookEventBuildLog = new WebhookEventBuildLog(
            count($webhooks),
            $businessEvent,
            $startTime,
            $endTimeBeforeSend
        );
        if ($jsonWebhookEventBuildLog = json_encode($webhookEventBuildLog->toLog())) {
            $this->logger->info($jsonWebhookEventBuildLog);
        }

        if ($isFake) {
            $this->client->bulkFakeSend($requests());
        } else {
            $this->client->bulkSend($requests());
        }
    }

    private function handleError(\Throwable $error, ActiveWebhook $webhook, BusinessEventInterface $businessEvent): void
    {
        if ($error instanceof WebhookEventDataBuilderNotFoundException) {
            $this->logger->info($error->getMessage());
        } elseif ($error instanceof EventBuildingExceptionInterface) {
            $webhookEventDataBuilderErrorLog = new WebhookEventDataBuilderErrorLog(
                $error->getMessage(),
                $webhook,
                $businessEvent
            );
            if ($jsonWebhookEventDataBuilderErrorLog = json_encode($webhookEventDataBuilderErrorLog->toLog())) {
                $this->logger->warning($jsonWebhookEventDataBuilderErrorLog);
            }
        } else {
            $webhookEventDataBuilderErrorLog = new WebhookEventDataBuilderErrorLog(
                (string)$error,
                $webhook,
                $businessEvent
            );
            if ($jsonWebhookEventDataBuilderErrorLog = json_encode($webhookEventDataBuilderErrorLog->toLog())) {
                $this->logger->critical($jsonWebhookEventDataBuilderErrorLog);
            }
        }
    }

    private function buildFakeActiveWebhook(int $userId): ActiveWebhook
    {
        return new ActiveWebhook(
            self::FAKE_CONNECTION_CODE,
            $userId,
            self::FAKE_SECRET,
            self::FAKE_URL
        );
    }
}
