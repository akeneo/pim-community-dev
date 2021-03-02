<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionEventBuildLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\EventBuildLogger;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class EventBuildLoggerSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger): void
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventBuildLogger::class);
    }

    public function it_logs_event_build(LoggerInterface $logger): void
    {
        $bulkEvent = new BulkEvent([
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

        $expectedLog = [
            'type' => 'event_api.event_build',
            'subscription_count' => 10,
            'event_count' => 2,
            'event_built_count' => 2,
            'duration_ms' => 100,
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
        ];

        $logger->info(json_encode($expectedLog, JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->log(10, 100, 2, $bulkEvent);
    }
}
