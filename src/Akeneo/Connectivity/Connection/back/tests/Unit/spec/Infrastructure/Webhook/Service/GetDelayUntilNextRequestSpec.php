<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectEventsApiRequestCountWithinLastHourQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\GetDelayUntilNextRequest;
use PhpSpec\ObjectBehavior;

class GetDelayUntilNextRequestSpec extends ObjectBehavior
{
    public function let(
        SelectEventsApiRequestCountWithinLastHourQuery $selectEventsApiRequestCountWithinLastHourQuery
    ): void {
        $this->beConstructedWith($selectEventsApiRequestCountWithinLastHourQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GetDelayUntilNextRequest::class);
    }

    public function it_returns_the_delay_until_next_request_even_when_there_is_no_entry(
        SelectEventsApiRequestCountWithinLastHourQuery $selectEventsApiRequestCountWithinLastHourQuery
    ) {
        $selectEventsApiRequestCountWithinLastHourQuery->execute(new \DateTimeImmutable('2021-01-08 10:12:30', new \DateTimeZone('UTC')))
            ->willReturn([]);

        $this->execute(new \DateTimeImmutable('2021-01-08 10:12:30', new \DateTimeZone('UTC')), 100)
            ->shouldReturn(0);
    }

    public function it_returns_the_delay_until_next_request(
        SelectEventsApiRequestCountWithinLastHourQuery $selectEventsApiRequestCountWithinLastHourQuery
    ) {
        $selectEventsApiRequestCountWithinLastHourQuery->execute(new \DateTimeImmutable('2021-01-08 11:02:10', new \DateTimeZone('UTC')))
            ->willReturn([
                [
                    'event_count' => 20,
                    'updated' => '2021-01-08 10:32:30',
                ],
                [
                    'event_count' => 90,
                    'updated' => '2021-01-08 10:11:30', // Limit will be reached here, minute xx:11:30 = 690 seconds.
                ],
            ]);

        $this->execute(new \DateTimeImmutable('2021-01-08 11:02:10', new \DateTimeZone('UTC')), 100)
            ->shouldReturn(560); // Current time minute is xx:02:10 = 130 seconds, so 690 - 130 = 560 seconds
    }
}
