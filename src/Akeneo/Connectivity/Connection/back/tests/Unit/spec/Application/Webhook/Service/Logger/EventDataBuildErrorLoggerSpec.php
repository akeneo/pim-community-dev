<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionEventBuildLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\EventDataBuildErrorLogger;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Platform\Component\EventQueue\Author;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class EventDataBuildErrorLoggerSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger): void
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventDataBuildErrorLogger::class);
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

        $this->log('Webhook event building failed', 'ecommerce', 42, $event);
    }
}
