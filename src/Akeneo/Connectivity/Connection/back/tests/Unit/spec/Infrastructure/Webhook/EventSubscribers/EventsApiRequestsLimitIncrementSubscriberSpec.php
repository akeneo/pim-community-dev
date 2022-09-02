<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestFailedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\UpdateEventsApiRequestCountQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers\EventsApiRequestsLimitIncrementSubscriber;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiRequestsLimitIncrementSubscriberSpec extends ObjectBehavior
{
    public function let(UpdateEventsApiRequestCountQueryInterface $eventsApiRequestCountQuery): void
    {
        $this->beConstructedWith($eventsApiRequestCountQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EventsApiRequestsLimitIncrementSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_message_processed_event_and_events_api_request_status_events(): void
    {
        $this->getSubscribedEvents()
            ->shouldReturn([
                EventsApiRequestSucceededEvent::class => 'incrementRequestCount',
                EventsApiRequestFailedEvent::class => 'incrementRequestCount',
                MessageProcessedEvent::class => 'saveRequestCount',
            ]);
    }

    public function it_increments_request_count(): void
    {
        $this->incrementRequestCount()
            ->shouldReturn(1);

        $this->incrementRequestCount()
            ->shouldReturn(2);
    }

    public function it_saves_request_count(UpdateEventsApiRequestCountQueryInterface $eventsApiRequestCountQuery): void
    {
        $eventsApiRequestCountQuery->execute(Argument::type(\DateTimeImmutable::class), 1)
            ->shouldBeCalled();

        $this->incrementRequestCount();
        $this->saveRequestCount();
    }

    public function it_resets_the_request_count_to_zero_after_saving_it(
        UpdateEventsApiRequestCountQueryInterface $eventsApiRequestCountQuery
    ): void {
        $eventsApiRequestCountQuery->execute(Argument::cetera())
            ->shouldBeCalled();

        $this->incrementRequestCount();
        $this->saveRequestCount();

        $this->incrementRequestCount()
            ->shouldReturn(1);
    }

    public function it_doesnt_save_request_count_of_zero(
        UpdateEventsApiRequestCountQueryInterface $eventsApiRequestCountQuery
    ): void {
        $eventsApiRequestCountQuery->execute(Argument::cetera())
            ->shouldNotBeCalled();

        $this->saveRequestCount();
    }
}
