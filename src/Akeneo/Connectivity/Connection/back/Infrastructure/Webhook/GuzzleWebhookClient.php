<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\WebhookClient;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
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
        $requests = [];
        foreach ($webhookRequests as $request) {
            $webhook = $request->webhook();
            $event = $request->event();
            $requests[] = $this->requestFactory->create(
                $webhook->url(),
                json_encode($event->normalize()),
                ['secret' => $webhook->secret()]
            );
        }

        $pool = new Pool($this->client, $requests, [
            'concurrency' => 5,
            'options' => [
                'timeout' => 3
            ],
            'fulfilled' => function (Response $response, $index) use ($requests)  {
                $this->logger->info(sprintf('request fulfilled'), $this->buildLogContext($requests[$index]));
            },
            'rejected' => function (RequestException $reason, $index) use ($requests) {
                $this->logger->error(sprintf('request rejected: %s', $reason->getMessage()), $this->buildLogContext($requests[$index]));
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function buildLogContext(Request $request)
    {
        $requestBody =  json_decode((string) $request->getBody(), true);

        return [
            'event_id' => $requestBody['event_id'],
            'event_date' => $requestBody['event_date'],
        ];
    }
}
