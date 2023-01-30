<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSendApiEventRequestLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiRequestLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\SendApiEventRequestLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClientInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestFailedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\RequestHeaders;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GuzzleWebhookClient implements WebhookClientInterface
{
    private ClientInterface $client;
    private EncoderInterface $encoder;
    private SendApiEventRequestLogger $sendApiEventRequestLogger;
    private EventsApiRequestLoggerInterface $debugLogger;
    private EventDispatcherInterface $eventDispatcher;

    /** @var array{concurrency: ?int, timeout: ?float} */
    private $config;

    /**
     * @param array{concurrency: ?int, timeout: ?float} $config
     */
    public function __construct(
        ClientInterface $client,
        EncoderInterface $encoder,
        SendApiEventRequestLogger $sendApiEventRequestLogger,
        EventsApiRequestLoggerInterface $debugLogger,
        EventDispatcherInterface $eventDispatcher,
        array $config
    ) {
        $this->client = $client;
        $this->encoder = $encoder;
        $this->sendApiEventRequestLogger = $sendApiEventRequestLogger;
        $this->debugLogger = $debugLogger;
        $this->eventDispatcher = $eventDispatcher;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function bulkSend(iterable $webhookRequests): void
    {
        $logs = [];

        $guzzleRequests = function () use (&$webhookRequests, &$logs) {
            foreach ($webhookRequests as $webhookRequest) {
                $body = $this->encoder->encode($webhookRequest->content(), 'json');

                $timestamp = \time();
                $signature = Signature::createSignature($webhookRequest->secret(), $timestamp, $body);

                $headers = [
                    'Content-Type' => 'application/json',
                    RequestHeaders::HEADER_REQUEST_SIGNATURE => $signature,
                    RequestHeaders::HEADER_REQUEST_TIMESTAMP => $timestamp,
                ];

                $logs[] = new EventSubscriptionSendApiEventRequestLog($webhookRequest, $headers, \microtime(true));

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
                    'allow_redirects' => false,
                ],
                'fulfilled' => function (Response $response, int $index) use (&$logs) {
                    /** @var EventSubscriptionSendApiEventRequestLog $webhookRequestLog */
                    $webhookRequestLog = $logs[$index];
                    $webhookRequestLog->setSuccess(true);
                    $webhookRequestLog->setEndTime(\microtime(true));
                    $webhookRequestLog->setResponse($response);

                    $pimEvents = \array_map(
                        static fn (WebhookEvent $apiEvent) => $apiEvent->getPimEvent(),
                        $webhookRequestLog->getWebhookRequest()->apiEvents()
                    );

                    $this->eventDispatcher->dispatch(new EventsApiRequestSucceededEvent(
                        $webhookRequestLog->getWebhookRequest()->webhook()->connectionCode(),
                        $pimEvents
                    ));

                    $this->debugLogger->logEventsApiRequestSucceed(
                        $webhookRequestLog->getWebhookRequest()->webhook()->connectionCode(),
                        $webhookRequestLog->getWebhookRequest()->apiEvents(),
                        $webhookRequestLog->getWebhookRequest()->url(),
                        $response->getStatusCode(),
                        $response->getHeaders(),
                    );
                },
                'rejected' => function (RequestException|ConnectException $reason, int $index) use (&$logs) {
                    $this->eventDispatcher->dispatch(new EventsApiRequestFailedEvent());

                    /** @var EventSubscriptionSendApiEventRequestLog $webhookRequestLog */
                    $webhookRequestLog = $logs[$index];
                    $webhookRequestLog->setMessage($reason->getMessage());
                    $webhookRequestLog->setSuccess(false);
                    $webhookRequestLog->setEndTime(\microtime(true));
                    if ($reason instanceof RequestException) {
                        $webhookRequestLog->setResponse($reason->getResponse());
                    }

                    $this->sendApiEventRequestLogger->log(
                        $webhookRequestLog->getWebhookRequest(),
                        $webhookRequestLog->getStartTime(),
                        $webhookRequestLog->getEndTime(),
                        $webhookRequestLog->getHeaders(),
                        $webhookRequestLog->getMessage(),
                        $webhookRequestLog->isSuccess(),
                        $webhookRequestLog->getResponse()
                    );

                    if ($reason instanceof RequestException && $reason->hasResponse()) {
                        $this->debugLogger->logEventsApiRequestFailed(
                            $webhookRequestLog->getWebhookRequest()->webhook()->connectionCode(),
                            $webhookRequestLog->getWebhookRequest()->apiEvents(),
                            (string) $reason->getRequest()->getUri(),
                            $reason->getResponse()->getStatusCode(),
                            $reason->getRequest()->getHeaders(),
                        );
                    } else {
                        $this->debugLogger->logEventsApiRequestTimedOut(
                            $webhookRequestLog->getWebhookRequest()->webhook()->connectionCode(),
                            $webhookRequestLog->getWebhookRequest()->apiEvents(),
                            (string) $reason->getRequest()->getUri(),
                            $this->config['timeout']
                        );
                    }
                },
            ]
        );

        $promise = $pool->promise();
        $promise->wait();
    }
}
