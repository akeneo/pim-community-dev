<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSendApiEventRequestLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiDebugResponseErrorLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\SendApiEventRequestLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\RequestHeaders;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GuzzleWebhookClient implements WebhookClient
{
    private ClientInterface $client;
    private EncoderInterface $encoder;
    private SendApiEventRequestLogger $sendApiEventRequestLogger;
    private EventsApiDebugResponseErrorLogger $responseErrorLogger;

    /** @var array{concurrency: ?int, timeout: ?float} */
    private $config;

    /**
     * @param array{concurrency: ?int, timeout: ?float} $config
     */
    public function __construct(
        ClientInterface $client,
        EncoderInterface $encoder,
        SendApiEventRequestLogger $sendApiEventRequestLogger,
        EventsApiDebugResponseErrorLogger $responseErrorLogger,
        array $config
    ) {
        $this->client = $client;
        $this->encoder = $encoder;
        $this->sendApiEventRequestLogger = $sendApiEventRequestLogger;
        $this->responseErrorLogger = $responseErrorLogger;
        $this->config = $config;
    }

    public function bulkSend(iterable $webhookRequests): void
    {
        $logs = [];

        $guzzleRequests = function () use (&$webhookRequests, &$logs) {
            foreach ($webhookRequests as $webhookRequest) {
                $body = $this->encoder->encode($webhookRequest->content(), 'json');

                $timestamp = time();
                $signature = Signature::createSignature($webhookRequest->secret(), $timestamp, $body);

                $headers = [
                    'Content-Type' => 'application/json',
                    RequestHeaders::HEADER_REQUEST_SIGNATURE => $signature,
                    RequestHeaders::HEADER_REQUEST_TIMESTAMP => $timestamp,
                ];

                $logs[] = new EventSubscriptionSendApiEventRequestLog($webhookRequest, $headers, microtime(true));

                $request = new Request('POST', $webhookRequest->url(), $headers, $body);

                yield $request;
            }
        };

        $pool = new Pool(
            $this->client,
            $guzzleRequests(),
            [
                'concurrency' => $this->config['concurrency'] ?? null,
                'options' => [
                    'timeout' => $this->config['timeout'] ?? null,
                ],
                'fulfilled' => function (Response $response, int $index) use (&$logs) {
                    /** @var EventSubscriptionSendApiEventRequestLog $webhookRequestLog */
                    $webhookRequestLog = $logs[$index];
                    $webhookRequestLog->setSuccess(true);
                    $webhookRequestLog->setEndTime(microtime(true));
                    $webhookRequestLog->setResponse($response);

                    $this->sendApiEventRequestLogger->log(
                        $webhookRequestLog->getWebhookRequest(),
                        $webhookRequestLog->getStartTime(),
                        $webhookRequestLog->getEndTime(),
                        $webhookRequestLog->getHeaders(),
                        $webhookRequestLog->getMessage(),
                        $webhookRequestLog->isSuccess(),
                        $webhookRequestLog->getResponse()
                    );
                },
                'rejected' => function (RequestException $reason, int $index) use (&$logs) {
                    /** @var EventSubscriptionSendApiEventRequestLog $webhookRequestLog */
                    $webhookRequestLog = $logs[$index];
                    $webhookRequestLog->setMessage($reason->getMessage());
                    $webhookRequestLog->setSuccess(false);
                    $webhookRequestLog->setEndTime(microtime(true));
                    $webhookRequestLog->setResponse($reason->getResponse());

                    $this->sendApiEventRequestLogger->log(
                        $webhookRequestLog->getWebhookRequest(),
                        $webhookRequestLog->getStartTime(),
                        $webhookRequestLog->getEndTime(),
                        $webhookRequestLog->getHeaders(),
                        $webhookRequestLog->getMessage(),
                        $webhookRequestLog->isSuccess(),
                        $webhookRequestLog->getResponse()
                    );

                    if ($reason->hasResponse()) {
                        $this->responseErrorLogger->logResponseError(
                            $webhookRequestLog->getWebhookRequest()->webhook()->connectionCode(),
                            $webhookRequestLog->getWebhookRequest()->apiEvents(),
                            strval($reason->getRequest()->getUri()),
                            $reason->getResponse()->getStatusCode(),
                            $reason->getRequest()->getHeaders(),
                        );
                    }
                },
            ]
        );

        $promise = $pool->promise();
        $promise->wait();
    }
}
