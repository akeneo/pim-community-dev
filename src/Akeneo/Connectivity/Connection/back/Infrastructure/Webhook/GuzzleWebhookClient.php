<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\WebhookClient;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

class GuzzleWebhookClient implements WebhookClient
{
    /** @var RequestFactory */
    private $requestFactory;

    /** @var ClientInterface */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ClientInterface $client,
        RequestFactory $requestFactory,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->logger = $logger;
    }

    /**
     * @param WebhookRequest[] $webhookRequests
     */
    public function bulkSend(array $webhookRequests): void
    {
        $buildRequests = function ($webhookRequests) {
            foreach ($webhookRequests as $request) {
                yield $this->requestFactory->create(
                    $request->getUrl(),
                    $request->getPayload(),
                    ['secret' => $request->getSecret()]
                );
            }
        };

        $pool = new Pool($this->client, $buildRequests($webhookRequests), [
            'concurrency' => 5,
            'options' => [
                'timeout' => 3
            ],
            'fulfilled' => function (Response $response, $index) {
                $this->logger->info(sprintf('%s fulfilled', $index));

            },
            'rejected' => function (RequestException $reason, $index) {
                $this->logger->error(sprintf('%s rejected : %s', $index, $reason->getMessage()));
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }
}
