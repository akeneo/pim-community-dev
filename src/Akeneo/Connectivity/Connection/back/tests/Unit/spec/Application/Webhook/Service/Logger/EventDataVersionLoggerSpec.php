<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\EventDataVersionLogger;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class EventDataVersionLoggerSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger): void
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventDataVersionLogger::class);
    }

    public function it_logs_event_data_version(LoggerInterface $logger): void
    {
        $expectedLog = [
            'type' => 'event_api.event_data_version',
            'version' => 'product_1234',
        ];

        $logger->info(json_encode($expectedLog, JSON_THROW_ON_ERROR))->shouldBeCalled();

        $this->log('product_1234');
    }
}
