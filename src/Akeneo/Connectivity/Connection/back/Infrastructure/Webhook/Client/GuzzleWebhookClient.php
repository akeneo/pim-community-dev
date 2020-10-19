<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GuzzleWebhookClient implements WebhookClient
{
    const HEADER_REQUEST_SIGNATURE = 'X-Akeneo-Request-Signature';
    const HEADER_REQUEST_TIMESTAMP = 'X-Akeneo-Request-Timestamp';

    /** @var ClientInterface */
    private $client;

    /** @var EncoderInterface */
    private $encoder;

    /** @var LoggerInterface */
    private $logger;

    /** @var array{concurrency: ?int, timeout: ?float} */
    private $config;

    /**
     * @param array{concurrency: ?int, timeout: ?float} $config
     */
    public function __construct(
        ClientInterface $client,
        EncoderInterface $encoder,
        LoggerInterface $logger,
        array $config
    ) {
        $this->client = $client;
        $this->encoder = $encoder;
        $this->logger = $logger;
        $this->config = $config;
    }

    public function bulkSend(iterable $webhookRequests): void
    {
        $logContexts = [];

        $guzzleRequests = function () use (&$webhookRequests, &$logContexts) {
            foreach ($webhookRequests as $webhookRequest) {
                $body = $this->encoder->encode($webhookRequest->content(), 'json');

                $timestamp = time();
                $signature = Signature::createSignature($webhookRequest->secret(), $body, $timestamp);

                $headers = [
                    'Content-Type' => 'application/json',
                    self::HEADER_REQUEST_SIGNATURE => $signature,
                    self::HEADER_REQUEST_TIMESTAMP => $timestamp,
                ];

                $logContexts[] = array_merge($webhookRequest->metadata(), ['request' => ['headers' => $headers]]);

                $request = new Request('POST', $webhookRequest->url(), $headers, $body);

                yield $request;
            }
        };

        $pool = new Pool($this->client, $guzzleRequests(), [
            'concurrency' => $this->config['concurrency'] ?? null,
            'options' => [
                'timeout' => $this->config['timeout'] ?? null
            ],
            'fulfilled' => function (Response $response, int $index) use (&$logContexts) {
                $this->logger->info(
                    'Webhook fulfilled',
                    array_merge($logContexts[$index], ['response' => $response->getStatusCode()])
                );
            },
            'rejected' => function (RequestException $reason, int $index) use (&$logContexts) {
                $response = $reason->getResponse();
                $this->logger->error(
                    'Webhook rejected with the following reason: ' . $reason->getMessage(),
                    array_merge(
                        $logContexts[$index],
                        ['response' => $response ? ['status_code' => $response->getStatusCode()] : null]
                    )
                );
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }
}
