<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectConnectionsWebhookQuery;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;

/**
 * @package   Akeneo\Connectivity\Connection\Application\WebHook\Command
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SendMessageToWebhooksHandler
{
    /** @var DbalSelectConnectionsWebhookQuery */
    private $selectConnectionsWebhookQuery;

    /** @var ClientInterface */
    private $client;

    /** @var RequestFactory */
    private $requestFactory;

    public function __construct(
        DbalSelectConnectionsWebhookQuery $selectConnectionsWebhookQuery,
        ClientInterface $client,
        RequestFactory $requestFactory
    ) {
        $this->selectConnectionsWebhookQuery = $selectConnectionsWebhookQuery;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    public function handle(SendMessageToWebhooksCommand $command): void
    {
        $webhooks = $this->selectConnectionsWebhookQuery->execute();

        $payload = [
            'event' => $command->businessEvent()->name(),
            'id' => $command->businessEvent()->uuid(),
            'data' => $command->businessEvent()->data()
        ];
        $body = json_encode($payload);

        // TODO : ADD SERVICE WebhookCLient

    }
}