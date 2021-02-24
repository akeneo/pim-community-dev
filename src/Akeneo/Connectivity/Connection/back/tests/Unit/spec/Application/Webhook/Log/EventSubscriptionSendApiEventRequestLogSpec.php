<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSendApiEventRequestLog;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventSubscriptionLog;
use Akeneo\Platform\Component\EventQueue\Author;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;

class EventSubscriptionSendApiEventRequestLogSpec extends ObjectBehavior
{
    public function let(): void
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

        $this->beConstructedWith(
            $webhookRequest,
            ['Content-Type' => 'application/json'],
            1603935007.832
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventSubscriptionSendApiEventRequestLog::class);
    }

    public function it_returns_the_log_with_response()
    {
        $this->setSuccess(true);
        $this->setEndTime(1603935029.121);
        $this->setResponse(new Response());

        $this->toLog()->shouldReturn([
            'type' => EventSubscriptionSendApiEventRequestLog::TYPE,
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
                    'timestamp' => 1577836800,
                ],
                [
                    'uuid' => '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.updated',
                    'timestamp' => 1577836811,
                ],
            ],
            'max_propagation_seconds' => 26098229,
            'min_propagation_seconds' => 26098218,
        ]);
    }

    public function it_returns_the_log_without_response()
    {
        $this->setMessage('a message');
        $this->setSuccess(false);
        $this->setEndTime(1603935029.121);
        $this->setResponse(null);

        $this->toLog()->shouldReturn([
            'type' => EventSubscriptionSendApiEventRequestLog::TYPE,
            'duration_ms' => 21289,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'message' => 'a message',
            'success' => false,
            'response' => null,
            'events' => [
                [
                    'uuid' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.created',
                    'timestamp' => 1577836800,
                ],
                [
                    'uuid' => '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.updated',
                    'timestamp' => 1577836811,
                ],
            ],
            'max_propagation_seconds' => 26098229,
            'min_propagation_seconds' => 26098218,
        ]);
    }

    public function it_returns_the_log_without_propagation_times()
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

        $this->beConstructedWith(
            $webhookRequest,
            ['Content-Type' => 'application/json'],
            1603935007.832
        );

        $this->setSuccess(true);
        $this->setEndTime(1603935029.121);
        $this->setResponse(new Response());

        $this->toLog()->shouldReturn([
            'type' => EventSubscriptionSendApiEventRequestLog::TYPE,
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
        ]);
    }

    public function it_throw_an_exception_when_end_time_is_null()
    {
        $this->shouldThrow(\RuntimeException::class)->during('toLog');
    }
}
