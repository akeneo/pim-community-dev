<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionEventBuildLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\ReachRequestLimitLogger;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class ReachRequestLimitLoggerSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger): void
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ReachRequestLimitLogger::class);
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

        $this->log(
            666,
            new \DateTimeImmutable('2021-01-01T00:00:00+00:00'),
            90)
        ;
    }
}
