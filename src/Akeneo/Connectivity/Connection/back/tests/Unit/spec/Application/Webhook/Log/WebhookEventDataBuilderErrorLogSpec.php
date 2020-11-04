<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\WebhookEventDataBuilderErrorLog;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Platform\Component\EventQueue\Author;
use PhpSpec\ObjectBehavior;

class WebhookEventDataBuilderErrorLogSpec extends ObjectBehavior
{
    public function let(): void
    {
        $author = Author::fromNameAndType('Julia', Author::TYPE_UI);

        $webhook = new ActiveWebhook(
            'ecommerce',
            1,
            'secret1234',
            'https://test.com'
        );

        $businessEvent = new ProductCreated(
            $author,
            [],
            1603935337,
            'fe904867-9428-4d97-bfa9-7aa13c0ee0bf'
        );

        $this->beConstructedWith(
            'Webhook event building failed',
            $webhook,
            $businessEvent
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebhookEventDataBuilderErrorLog::class);
    }

    public function it_returns_the_log()
    {
        $this->toLog()->shouldReturn([
            'type' => 'webhook.event_build',
            'message' => 'Webhook event building failed',
            'webhook' => [
                'connection_code' => 'ecommerce',
                'user_id' => 1,
            ],
            'event' => [
                'uuid' => 'fe904867-9428-4d97-bfa9-7aa13c0ee0bf',
                'author' => 'Julia',
                'author_type' => 'ui',
                'name' => 'product.created',
                'timestamp' => 1603935337,
            ],
        ]);
    }
}
