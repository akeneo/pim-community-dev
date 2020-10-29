<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\WebhookRequestLog;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use PhpSpec\ObjectBehavior;

class WebhookRequestLogSpec extends ObjectBehavior
{
    public function let(): void
    {
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook');
        $event = new WebhookEvent(
            'product.created',
            '79fc4791-86d6-4d3b-93c5-76b787af9497',
            '2020-01-01T00:00:00+00:00',
            'Julia',
            'staging.akeneo.com',
            ['data']
        );

        $webhookRequest = new WebhookRequest($webhook, $event);

        $this->beConstructedWith(
            $webhookRequest,
            1603935007.832,
            1603935029.121
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebhookRequestLog::class);
    }

    public function it_returns_the_log()
    {
        $this->toLog()->shouldReturn([
            'type' => 'webhook.send_request',
            'monitor' => [
                'duration' => '21.289000034332',
            ],
            'business_event' => [
                'uuid' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                'author' => 'Julia',
                'name' => 'product.created',
                'timestamp' => 1577836800,
            ],
        ]);
    }
}
