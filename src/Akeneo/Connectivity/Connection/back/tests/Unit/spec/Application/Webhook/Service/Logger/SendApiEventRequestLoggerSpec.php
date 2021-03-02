<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSendApiEventRequestLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\SendApiEventRequestLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\Author;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class SendApiEventRequestLoggerSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger): void
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SendApiEventRequestLogger::class);
    }

    public function it_logs_send_api_event_request_with_response(LoggerInterface $logger): void
    {
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook');
        $events = [
            new WebhookEvent(
                'product.created',
                '79fc4791-86d6-4d3b-93c5-76b787af9497',
                '2020-01-01T00:00:00+00:00',
                Author::fromNameAndType('julia', Author::TYPE_UI),
                'staging.akeneo.com',
                ['data']
            ),
            new WebhookEvent(
                'product.updated',
                '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                '2020-01-01T00:00:11+00:00',
                Author::fromNameAndType('julia', Author::TYPE_UI),
                'staging.akeneo.com',
                ['data']
            )
        ];

        $webhookRequest = new WebhookRequest($webhook, $events);

        $eventSubscriptionSendApiEventRequestLog = new EventSubscriptionSendApiEventRequestLog(
            $webhookRequest,
            ['Content-Type' => 'application/json'],
            1603935007.832
        );
        $eventSubscriptionSendApiEventRequestLog->setEndTime(1603935008.832);
        $eventSubscriptionSendApiEventRequestLog->setMessage('a message');
        $eventSubscriptionSendApiEventRequestLog->setResponse(new Response());
        $eventSubscriptionSendApiEventRequestLog->setSuccess(true);

        $expectedLog = [
            'type' => 'event_api.send_api_event_request',
            'duration_ms' => 1000,
            'headers' => ['Content-Type' => 'application/json'],
            'message' => 'a message',
            'success' => true,
            'response' => [
                'status_code' => 200,
            ],
            'events' => [
                [
                    'uuid' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.created',
                    'timestamp' => 1577836800
                ],
                [
                    'uuid' => '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.updated',
                    'timestamp' => 1577836811,
                ]
            ],
            'max_propagation_seconds' => 26098208,
            'min_propagation_seconds' => 26098197,
        ];

        $logger->info(json_encode($expectedLog, JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->log(
            $eventSubscriptionSendApiEventRequestLog->getWebhookRequest(),
            $eventSubscriptionSendApiEventRequestLog->getStartTime(),
            $eventSubscriptionSendApiEventRequestLog->getEndTime(),
            $eventSubscriptionSendApiEventRequestLog->getHeaders(),
            $eventSubscriptionSendApiEventRequestLog->getMessage(),
            $eventSubscriptionSendApiEventRequestLog->isSuccess(),
            $eventSubscriptionSendApiEventRequestLog->getResponse()
        );
    }

    public function it_logs_send_api_event_request_without_response(LoggerInterface $logger): void
    {
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook');
        $events = [
            new WebhookEvent(
                'product.created',
                '79fc4791-86d6-4d3b-93c5-76b787af9497',
                '2020-01-01T00:00:00+00:00',
                Author::fromNameAndType('julia', Author::TYPE_UI),
                'staging.akeneo.com',
                ['data']
            ),
            new WebhookEvent(
                'product.updated',
                '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                '2020-01-01T00:00:11+00:00',
                Author::fromNameAndType('julia', Author::TYPE_UI),
                'staging.akeneo.com',
                ['data']
            )
        ];

        $webhookRequest = new WebhookRequest($webhook, $events);

        $eventSubscriptionSendApiEventRequestLog = new EventSubscriptionSendApiEventRequestLog(
            $webhookRequest,
            ['Content-Type' => 'application/json'],
            1603935007.832
        );
        $eventSubscriptionSendApiEventRequestLog->setEndTime(1603935008.832);
        $eventSubscriptionSendApiEventRequestLog->setMessage('a message');
        $eventSubscriptionSendApiEventRequestLog->setResponse(null);
        $eventSubscriptionSendApiEventRequestLog->setSuccess(false);

        $expectedLog = [
            'type' => 'event_api.send_api_event_request',
            'duration_ms' => 1000,
            'headers' => ['Content-Type' => 'application/json'],
            'message' => 'a message',
            'success' => false,
            'response' => null,
            'events' => [
                [
                    'uuid' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.created',
                    'timestamp' => 1577836800
                ],
                [
                    'uuid' => '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.updated',
                    'timestamp' => 1577836811,
                ]
            ],
            'max_propagation_seconds' => 26098208,
            'min_propagation_seconds' => 26098197,
        ];

        $logger->info(json_encode($expectedLog, JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->log(
            $eventSubscriptionSendApiEventRequestLog->getWebhookRequest(),
            $eventSubscriptionSendApiEventRequestLog->getStartTime(),
            $eventSubscriptionSendApiEventRequestLog->getEndTime(),
            $eventSubscriptionSendApiEventRequestLog->getHeaders(),
            $eventSubscriptionSendApiEventRequestLog->getMessage(),
            $eventSubscriptionSendApiEventRequestLog->isSuccess(),
            $eventSubscriptionSendApiEventRequestLog->getResponse()
        );
    }

    public function it_returns_the_log_without_propagation_times(LoggerInterface $logger)
    {
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook');
        $events = [
            new WebhookEvent(
                'product.created',
                '79fc4791-86d6-4d3b-93c5-76b787af9497',
                'NOT_WELL_FORMED',
                Author::fromNameAndType('julia', Author::TYPE_UI),
                'staging.akeneo.com',
                ['data']
            ),
            new WebhookEvent(
                'product.updated',
                '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                'NOT_WELL_FORMED',
                Author::fromNameAndType('julia', Author::TYPE_UI),
                'staging.akeneo.com',
                ['data']
            )
        ];

        $webhookRequest = new WebhookRequest($webhook, $events);

        $eventSubscriptionSendApiEventRequestLog = new EventSubscriptionSendApiEventRequestLog(
            $webhookRequest,
            ['Content-Type' => 'application/json'],
            1603935007.832
        );
        $eventSubscriptionSendApiEventRequestLog->setEndTime(1603935029.121);
        $eventSubscriptionSendApiEventRequestLog->setResponse(new Response());
        $eventSubscriptionSendApiEventRequestLog->setSuccess(true);

        $expectedLog = [
            'type' => 'event_api.send_api_event_request',
            'duration_ms' => 21289,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'message' => '',
            'success' => true,
            'response' => [
                'status_code' => 200,
            ],
            'events' => [
                [
                    'uuid' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.created',
                    'timestamp' => null,
                ],
                [
                    'uuid' => '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.updated',
                    'timestamp' => null,
                ],
            ],
        ];

        $logger->info(json_encode($expectedLog, JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->log(
            $eventSubscriptionSendApiEventRequestLog->getWebhookRequest(),
            $eventSubscriptionSendApiEventRequestLog->getStartTime(),
            $eventSubscriptionSendApiEventRequestLog->getEndTime(),
            $eventSubscriptionSendApiEventRequestLog->getHeaders(),
            $eventSubscriptionSendApiEventRequestLog->getMessage(),
            $eventSubscriptionSendApiEventRequestLog->isSuccess(),
            $eventSubscriptionSendApiEventRequestLog->getResponse()
        );
    }
}
