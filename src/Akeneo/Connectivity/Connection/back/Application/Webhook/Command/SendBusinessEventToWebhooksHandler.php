<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\WebhookEvent\WebhookEventBuildingFailedException;
use Psr\Log\LoggerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SendBusinessEventToWebhooksHandler
{
    /** @var SelectActiveWebhooksQuery */
    private $selectActiveWebhooksQuery;

    /** @var WebhookClient */
    private $client;

    /** @var WebhookEventBuilder */
    private $builder;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        WebhookClient $client,
        WebhookEventBuilder $builder,
        LoggerInterface $logger
    ) {
        $this->selectActiveWebhooksQuery = $selectActiveWebhooksQuery;
        $this->client = $client;
        $this->builder = $builder;
        $this->logger = $logger;
    }

    public function handle(SendBusinessEventToWebhooksCommand $command): void
    {
        $webhooks = $this->selectActiveWebhooksQuery->execute();
        if (0 === count($webhooks)) {
            return;
        }

        $businessEvent = $command->businessEvent();

        $requests = function () use ($businessEvent, $webhooks) {
            foreach ($webhooks as $webhook) {
                $context = [
                    'user_id' => $webhook->userId()
                ];
                try {
                    $event = $this->builder->build($businessEvent, $context);
                } catch (WebhookEventBuildingFailedException $exception) {
                    $this->logger->error($exception->getMessage(), $exception->getContext());

                    continue;
                }

                yield new WebhookRequest($webhook, $event);
            }
        };

        $this->client->bulkSend($requests());
    }
}
