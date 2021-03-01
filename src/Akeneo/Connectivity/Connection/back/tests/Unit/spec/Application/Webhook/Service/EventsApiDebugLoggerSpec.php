<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiDebugLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugLoggerSpec extends ObjectBehavior
{
    public function let(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00'))
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventsApiDebugLogger::class);
    }

    public function it_logs_the_limit_of_event_api_requests_reached(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            1
        );

        $eventsApiDebugRepository->bulkInsert([
            [
                'timestamp' => 1609459200,
                'level' => 'warning',
                'message' => 'The maximum number of events sent per hour has been reached.',
                'connection_code' => null,
                'context' => [],
            ]
        ])
            ->shouldBeCalled();

        $this->logLimitOfEventApiRequestsReached();
    }

    public function it_flushs_logs_in_the_buffer(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $eventsApiDebugRepository->bulkInsert(Argument::size(4))
            ->shouldBeCalled();

        $this->logLimitOfEventApiRequestsReached();
        $this->logLimitOfEventApiRequestsReached();
        $this->logLimitOfEventApiRequestsReached();
        $this->logLimitOfEventApiRequestsReached();
        $this->flushLogs();
    }

    public function it_flushs_logs_once_the_buffer_is_full(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            2
        );

        $eventsApiDebugRepository->bulkInsert(Argument::size(2))
            ->shouldBeCalledTimes(2);

        $this->logLimitOfEventApiRequestsReached();
        $this->logLimitOfEventApiRequestsReached();
        $this->logLimitOfEventApiRequestsReached();
        $this->logLimitOfEventApiRequestsReached();
    }
}
