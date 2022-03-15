<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\LimitOfEventsApiRequestsReachedLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\ReachRequestLimitLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers\EventsApiRequestsLimitEventSubscriber;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\GetDelayUntilNextRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\Sleep;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventsApiRequestsLimitEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        GetDelayUntilNextRequest $getDelayUntilNextRequest,
        Sleep $sleep,
        ReachRequestLimitLogger $reachRequestLimitLogger,
        LimitOfEventsApiRequestsReachedLoggerInterface $limitOfEventsApiRequestsReachedLogger,
        EventsApiDebugRepositoryInterface $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $getDelayUntilNextRequest,
            10,
            $sleep,
            $reachRequestLimitLogger,
            $limitOfEventsApiRequestsReachedLogger,
            $eventsApiDebugRepository
        );
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            MessageProcessedEvent::class => 'checkWebhookRequestLimit',
        ]);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldHaveType(EventsApiRequestsLimitEventSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_sleeps_until_the_delay_expire_when_limit_is_reached(
        GetDelayUntilNextRequest $getDelayUntilNextRequest,
        Sleep $sleep,
        ReachRequestLimitLogger $reachRequestLimitLogger
    ): void {
        $getDelayUntilNextRequest
            ->execute(Argument::type(\DateTimeImmutable::class), 10)
            ->willReturn(123);

        $reachRequestLimitLogger->log(10, Argument::type('\DateTimeImmutable'), 123)->shouldBeCalled();
        $sleep->sleep(123)->shouldBeCalled();

        $this->checkWebhookRequestLimit();
    }

    public function it_logs_for_the_events_api_debug_that_the_limit_is_reached(
        GetDelayUntilNextRequest $getDelayUntilNextRequest,
        LimitOfEventsApiRequestsReachedLoggerInterface $limitOfEventsApiRequestsReachedLogger,
        EventsApiDebugRepositoryInterface $eventsApiDebugRepository
    ): void {
        $getDelayUntilNextRequest
            ->execute(Argument::cetera())
            ->willReturn(1);

        $limitOfEventsApiRequestsReachedLogger->logLimitOfEventsApiRequestsReached()
            ->shouldBeCalled();
        $eventsApiDebugRepository->flush()
            ->shouldBeCalled();

        $this->checkWebhookRequestLimit();
    }
}
