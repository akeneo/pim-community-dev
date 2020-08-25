<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\MessageHandler;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectConnectionsWebhookQuery;
use Akeneo\Pim\Enrichment\Bundle\Message\BusinessEvent;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class BusinessEventHandler implements MessageSubscriberInterface
{
    private $selectConnectionsWebhookQuery;
    private $client;
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

    public static function getHandledMessages(): iterable
    {
        yield BusinessEvent::class => [
            'from_transport' => 'webhook'
        ];
    }

    public function __invoke(BusinessEvent $event)
    {
        $webhooks = $this->selectConnectionsWebhookQuery->execute();

        $payload = [
            'event' => $event->getName(),
            'id' => $event->getUuid(),
            'data' => $event->getData()
        ];
        $body = json_encode($payload);

        $requests = function ($webhooks, $body) {
            foreach ($webhooks as $webhook) {
                yield $this->requestFactory->create(
                    // http://172.17.0.1:8000/webhook
                    $webhook['webhook_url'],
                    $body,
                    [
                        // NjVlODRiZTMzNTMyZmI3ODRjNDgxMjk2NzVmOWVmZjNhNjgyYjI3MTY4YzBlYTc0NGIyY2Y1OGVlMDIzMzdjNQ==
                        'secret' => $webhook['webhook_secret'],
                    ]
                );
            }
        };

        $pool = new Pool($this->client, $requests($webhooks, $body), [
            'concurrency' => 5,
            'options' => [
                'timeout' => 3
            ],
            'fulfilled' => function (Response $response, $index) {
                echo sprintf('%s fulfilled', $index);
            },
            'rejected' => function (RequestException $reason, $index) {
                echo sprintf('%s rejected : %s', $index, $reason->getMessage());
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }
}
