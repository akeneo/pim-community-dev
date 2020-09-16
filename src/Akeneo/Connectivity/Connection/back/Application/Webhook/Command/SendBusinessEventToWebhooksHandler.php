<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;

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

    public function __construct(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        WebhookClient $client,
        WebhookEventBuilder $builder
    ) {
        $this->selectActiveWebhooksQuery = $selectActiveWebhooksQuery;
        $this->client = $client;
        $this->builder = $builder;
    }

    public function handle(SendBusinessEventToWebhooksCommand $command): void
    {
        $webhooks = $this->selectActiveWebhooksQuery->execute();
        if (0 === count($webhooks)) {
            return;
        }

        $requests = function () use ($command, $webhooks) {
            foreach ($webhooks as $webhook) {
                $event = $this->builder->build($command->businessEvent(), [
                    'user_id' => $webhook->userId()
                ]);

                yield new WebhookRequest($webhook, $event);
            }
        };

        $this->client->bulkSend($requests());
    }
}
