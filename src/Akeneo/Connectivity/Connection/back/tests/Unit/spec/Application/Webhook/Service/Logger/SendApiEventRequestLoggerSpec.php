<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSendApiEventRequestLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\SendApiEventRequestLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\Author;
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

    public function it_logs_send_api_event_request(LoggerInterface $logger): void
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

        $logger->info(json_encode($eventSubscriptionSendApiEventRequestLog->toLog(), JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->log($eventSubscriptionSendApiEventRequestLog);
    }
}
