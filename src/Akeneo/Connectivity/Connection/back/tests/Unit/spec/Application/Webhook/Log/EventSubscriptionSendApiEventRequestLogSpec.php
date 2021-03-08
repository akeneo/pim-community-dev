<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSendApiEventRequestLog;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventSubscriptionLog;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;

class EventSubscriptionSendApiEventRequestLogSpec extends ObjectBehavior
{
    public function let(): void
    {
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook');
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $events = [
            new WebhookEvent(
                'product.created',
                '79fc4791-86d6-4d3b-93c5-76b787af9497',
                '2020-01-01T00:00:00+00:00',
                $author,
                'staging.akeneo.com',
                ['data'],
                $this->createEvent($author, ['data'])

            ),
            new WebhookEvent(
                'product.updated',
                '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                '2020-01-01T00:00:11+00:00',
                $author,
                'staging.akeneo.com',
                ['data'],
                $this->createEvent($author, ['data'])
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

    public function it_returns_the_start_time(): void
    {
        $this->getStartTime()->shouldReturn(1603935007.832);
    }

    public function it_returns_the_headers(): void
    {
        $this->getHeaders()->shouldReturn(['Content-Type' => 'application/json']);
    }

    public function it_returns_the_webhook_requests(): void
    {
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook');
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $events = [
            new WebhookEvent(
                'product.created',
                '79fc4791-86d6-4d3b-93c5-76b787af9497',
                '2020-01-01T00:00:00+00:00',
                $author,
                'staging.akeneo.com',
                ['data'],
                $this->createEvent($author, ['data'])
            ),
            new WebhookEvent(
                'product.updated',
                '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                '2020-01-01T00:00:11+00:00',
                $author,
                'staging.akeneo.com',
                ['data'],
                $this->createEvent($author, ['data'])
            )
        ];

        $webhookRequest = new WebhookRequest($webhook, $events);

        $this->beConstructedWith(
            $webhookRequest,
            ['Content-Type' => 'application/json'],
            1603935007.832
        );

        $this->getWebhookRequest()->shouldReturn($webhookRequest);
    }

    public function it_returns_the_message(): void
    {
        $this->setMessage('a message');

        $this->getMessage()->shouldReturn('a message');
    }

    public function it_returns_success(): void
    {
        $this->setSuccess(true);

        $this->isSuccess()->shouldReturn(true);
    }

    public function it_returns_the_end_time(): void
    {
        $this->setEndTime(1603935009.832);

        $this->getEndTime()->shouldReturn(1603935009.832);
    }

    public function it_returns_the_response(): void
    {
        $response = new Response();
        $this->setResponse($response);

        $this->getResponse()->shouldReturn($response);
    }

    private function createEvent(Author $author, array $data): EventInterface
    {
        $timestamp = 1577836800;
        $uuid = '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c';

        return new class($author, $data, $timestamp, $uuid) extends Event
        {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}
