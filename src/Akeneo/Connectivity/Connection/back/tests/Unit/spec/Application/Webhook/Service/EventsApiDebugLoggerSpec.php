<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiDebugLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugLoggerSpec extends ObjectBehavior
{
    public function let(EventsApiDebugRepository $eventsApiDebugRepository): void
    {
        $this->beConstructedWith($eventsApiDebugRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventsApiDebugLogger::class);
    }

    public function it_logs_the_limit_of_event_api_requests_reached(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith($eventsApiDebugRepository, 1);

        $eventsApiDebugRepository->bulkInsert([
            [
                'timestamp' => 946684800,
                'level' => 'warning',
                'message' => 'The maximum number of events sent per hour has been reached.',
                'connection_code' => null,
                'context' => [],
            ]
        ])
            ->shouldBeCalled();

        $this->logLimitOfEventApiRequestsReached(946684800);
    }

    public function it_flushs_logs_in_the_buffer(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $eventsApiDebugRepository->bulkInsert(Argument::size(4))
            ->shouldBeCalled();

        $this->logLimitOfEventApiRequestsReached(0);
        $this->logLimitOfEventApiRequestsReached(315532800);
        $this->logLimitOfEventApiRequestsReached(631152000);
        $this->logLimitOfEventApiRequestsReached(946684800);
        $this->flushLogs();
    }

    public function it_flushs_logs_once_the_buffer_is_full(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith($eventsApiDebugRepository, 2);

        $eventsApiDebugRepository->bulkInsert(Argument::size(2))
            ->shouldBeCalledTimes(2);

        $this->logLimitOfEventApiRequestsReached(0);
        $this->logLimitOfEventApiRequestsReached(315532800);
        $this->logLimitOfEventApiRequestsReached(631152000);
        $this->logLimitOfEventApiRequestsReached(946684800);
    }
}
