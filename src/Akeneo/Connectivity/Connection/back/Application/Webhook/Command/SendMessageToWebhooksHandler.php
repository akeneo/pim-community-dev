<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectConnectionsWebhookQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\GuzzleWebhookClient;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;

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

    /** @var RequestFactory */
    private $requestFactory;

    public function __construct(
        SelectConnectionsWebhookQuery $selectConnectionsWebhookQuery,
        GuzzleWebhookClient $client,
        RequestFactory $requestFactory
    ) {
        $this->selectConnectionsWebhookQuery = $selectConnectionsWebhookQuery;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    public function handle(SendMessageToWebhooksCommand $command): void
    {
        try {
            $webhooks = $this->selectConnectionsWebhookQuery->execute();
        } catch (\Exception $e) {
            // YoLO
        }

//        foreach ($webhooks as $webhook) {
//            $registry->build($webhook, $command->businessEvent());
//        }

//        $payload = [
//            'event' => $command->businessEvent()->name(),
//            'id' => $command->businessEvent()->uuid(),
//            'data' => $command->businessEvent()->data()
//        ];
//        $body = json_encode($payload);

        $this->client->bulkSend($webhookRequests);

    }
}
