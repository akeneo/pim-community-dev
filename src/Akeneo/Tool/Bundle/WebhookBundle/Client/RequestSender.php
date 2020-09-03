<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\WebhookBundle\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestSender
{
    /** @var ClientInterface */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param Request[] $requests
     */
    public function send(array $requests): void
    {
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