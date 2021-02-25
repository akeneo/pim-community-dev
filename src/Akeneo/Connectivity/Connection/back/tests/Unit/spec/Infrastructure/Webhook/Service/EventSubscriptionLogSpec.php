<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionEventBuildLog;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventSubscriptionLog;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class EventSubscriptionLogSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger): void
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventSubscriptionLog::class);
    }

    public function it_logs_event_data_build_error(LoggerInterface $logger): void
    {
        $event = new ProductCreated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['identifier' => '1'],
            1603935337,
            'fe904867-9428-4d97-bfa9-7aa13c0ee0bf'
        );

        $expectedLog = [
            'type' => 'event_api.event_data_build_error',
            'message' => 'Webhook event building failed',
            'connection_code' => 'ecommerce',
            'user_id' => 42,
            'event' => [
                'uuid' => 'fe904867-9428-4d97-bfa9-7aa13c0ee0bf',
                'author' => 'julia',
                'author_type' => 'ui',
                'name' => 'product.created',
                'timestamp' => 1603935337,
            ],
        ];

        $logger->warning(json_encode($expectedLog, JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->logEventDataBuildError('Webhook event building failed', 'ecommerce', 42, $event);
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

        $this->logEventBuild(10, 100, 2, $bulkEvent);
    }

    public function it_logs_event_requests_limit_reached(LoggerInterface $logger): void
    {
        $expectedLog = [
            'type' => 'event_api.reach_requests_limit',
            'message' => 'event subscription requests limit has been reached',
            'limit' => 666,
            'retry_after_seconds' => 90,
            'limit_reset' => '2021-01-01T00:01:30+00:00'
        ];

        $logger->info(json_encode($expectedLog, JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->logReachRequestLimit(
            666,
            new \DateTimeImmutable('2021-01-01T00:00:00+00:00'),
            90)
        ;
    }

    public function it_logs_skip_own_event(LoggerInterface $logger): void
    {
        $event = new ProductCreated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['identifier' => '1'],
            1603935337,
            'fe904867-9428-4d97-bfa9-7aa13c0ee0bf'
        );

        $expectedLog = [
            'type' => 'event_api.skip_own_event',
            'connection_code' => 'ecommerce',
            'event' => [
                'uuid' => 'fe904867-9428-4d97-bfa9-7aa13c0ee0bf',
                'author' => 'julia',
                'author_type' => 'ui',
                'name' => 'product.created',
                'timestamp' => 1603935337,
            ],
        ];

        $logger->info(json_encode($expectedLog, JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->logSkipOwnEvent($event,'ecommerce');
    }

    public function truc(LoggerInterface $logger): void
    {
        $logger->info(Argument::type('Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSendApiEventRequestLog'))
            ->shouldBeCalled();

        $this->logSendApiEventRequest();
    }
}
