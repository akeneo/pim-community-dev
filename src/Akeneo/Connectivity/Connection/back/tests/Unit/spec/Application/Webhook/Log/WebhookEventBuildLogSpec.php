<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\WebhookEventBuildLog;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use PhpSpec\ObjectBehavior;

class WebhookEventBuildLogSpec extends ObjectBehavior
{
    public function let(): void
    {
        $event = new BulkEvent([
            new ProductCreated(
                Author::fromNameAndType('julia', Author::TYPE_UI),
                ['identifier' => '1'],
                1603935337,
                'fe904867-9428-4d97-bfa9-7aa13c0ee0bf'
            ),
            new ProductUpdated(
                Author::fromNameAndType('julia', Author::TYPE_UI),
                ['identifier' => '2'],
                1603935338,
                '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3'
            )
        ]);

        $this->beConstructedWith(
            10,
            $event,
            1603935007.832,
            1603935029.121
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebhookEventBuildLog::class);
    }

    public function it_returns_the_log(): void
    {
        $this->toLog()->shouldReturn([
            'type' => 'webhook.event_build',
            'subscription_count' => 10,
            'event_count' => 2,
            'duration' => 21289,
            'events' => [
                [
                    'uuid' => 'fe904867-9428-4d97-bfa9-7aa13c0ee0bf',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.created',
                    'timestamp' => 1603935337,
                ],
                [
                    'uuid' => '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.updated',
                    'timestamp' => 1603935338,
                ]
            ],
        ]);
    }

    public function it_returns_the_log_for_a_single_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $event = new ProductCreated(
            $author,
            ['identifier' => '1'],
            1603935337,
            'fe904867-9428-4d97-bfa9-7aa13c0ee0bf'
        );

        $this->beConstructedWith(
            10,
            $event,
            1603935007.832,
            1603935029.121
        );

        $this->toLog()->shouldReturn([
            'type' => 'webhook.event_build',
            'subscription_count' => 10,
            'event_count' => 1,
            'duration' => 21289,
            'events' => [
                [
                    'uuid' => 'fe904867-9428-4d97-bfa9-7aa13c0ee0bf',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.created',
                    'timestamp' => 1603935337,
                ]
            ],
        ]);
    }
}
