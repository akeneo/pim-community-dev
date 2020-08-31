<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectConnectionsWebhookQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\GuzzleWebhookClient;

/**
 * @package   Akeneo\Connectivity\Connection\Application\WebHook\Command
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SendMessageToWebhooksHandler
{
    /** @var SelectConnectionsWebhookQuery */
    private $selectConnectionsWebhookQuery;

    /** @var GuzzleWebhookClient */
    private $client;

    /** @var WebhookEventBuilder */
    private $builder;

    public function __construct(
        SelectConnectionsWebhookQuery $selectConnectionsWebhookQuery,
        GuzzleWebhookClient $client,
        WebhookEventBuilder $builder
    ) {
        $this->selectConnectionsWebhookQuery = $selectConnectionsWebhookQuery;
        $this->client = $client;
        $this->builder = $builder;
    }

    public function handle(SendMessageToWebhooksCommand $command): void
    {
        try {
            $webhooks = $this->selectConnectionsWebhookQuery->execute();
        } catch (\Exception $e) {
            // YoLO
        }

        $webhookRequests = [];
        
        foreach ($webhooks as $webhook) {
            $webhookRequests[] = new WebhookRequest(
                $webhook,
                $this->builder->build($webhook, $command->businessEvent())
            );
        }

        $this->client->bulkSend($webhookRequests);

    }
}
