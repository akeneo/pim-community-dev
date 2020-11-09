<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\WebhookEventBuildLog;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Platform\Component\EventQueue\Author;
use PhpSpec\ObjectBehavior;

class WebhookEventBuildLogSpec extends ObjectBehavior
{
    public function let(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $businessEvent = new ProductCreated(
            $author,
            ['identifier' => '1'],
            1603935337,
            'fe904867-9428-4d97-bfa9-7aa13c0ee0bf'
        );

        $this->beConstructedWith(
            10,
            $businessEvent,
            1603935007.832,
            1603935029.121
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebhookEventBuildLog::class);
    }

    public function it_returns_the_log()
    {
        $this->toLog()->shouldReturn([
            'type' => 'webhook.event_build',
            'webhook_event_build_count' => 10,
            'duration' => 21289,
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
