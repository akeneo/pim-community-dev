<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use function Akeneo\Connectivity\Connection\Application\Webhook\Command\;

class WebhookClient
{
    /** @var RequestFactory */
    private $requestFactory;

    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client, RequestFactory $requestFactory) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    public function bulkSend(array $queries): void
    {
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