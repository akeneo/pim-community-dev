<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionDataBuildErrorLog;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use PhpSpec\ObjectBehavior;

class EventSubscriptionDataBuildErrorLogSpec extends ObjectBehavior
{
    public function let(): void
    {
        $event = new BulkEvent(
            [
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
                ),
            ]
        );

        $this->beConstructedWith('Webhook event building failed', 'ecommerce', 42, $event);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventSubscriptionDataBuildErrorLog::class);
    }

    public function it_returns_the_log(): void
    {
        $this->toLog()->shouldReturn(
            [
                'type' => EventSubscriptionDataBuildErrorLog::TYPE,
                'message' => 'Webhook event building failed',
                'connection_code' => 'ecommerce',
                'user_id' => 42,
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
                    ],
                ],
            ]
        );
    }

    public function it_returns_the_log_for_a_single_event(): void
    {
        $event = new BulkEvent(
            [
                new ProductCreated(
                    Author::fromNameAndType('julia', Author::TYPE_UI),
                    ['identifier' => '1'],
                    1603935337,
                    'fe904867-9428-4d97-bfa9-7aa13c0ee0bf'
                ),
            ]
        );

        $this->beConstructedWith('Webhook event building failed', 'ecommerce', 42, $event);

        $this->toLog()->shouldReturn(
            [
                'type' => EventSubscriptionDataBuildErrorLog::TYPE,
                'message' => 'Webhook event building failed',
                'connection_code' => 'ecommerce',
                'user_id' => 42,
                'events' => [
                    [
                        'uuid' => 'fe904867-9428-4d97-bfa9-7aa13c0ee0bf',
                        'author' => 'julia',
                        'author_type' => 'ui',
                        'name' => 'product.created',
                        'timestamp' => 1603935337,
                    ],
                ],
            ]
        );
    }
}
