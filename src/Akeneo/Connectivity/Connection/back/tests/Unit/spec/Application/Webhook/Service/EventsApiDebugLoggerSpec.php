<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiDebugLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\EventNormalizer;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
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
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            []
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventsApiDebugLogger::class);
    }

    public function it_logs_when_the_event_subscription_skipped_its_own_event(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );

        $eventsApiDebugRepository->persist([
                'timestamp' => 1609459200,
                'level' => 'notice',
                'message' => 'The event was not sent because it was raised by the same connection.',
                'connection_code' => 'erp_000',
                'context' => [
                    'event' => [
                        'action' => 'my_event',
                        'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                        'event_datetime' => '1970-01-01T00:00:00+00:00',
                        'author' => 'julia',
                        'author_type' => 'ui',
                    ]
                ],
            ])
            ->shouldBeCalled();

        $this->logEventSubscriptionSkippedOwnEvent('erp_000', $this->createEvent());
    }

    public function it_logs_when_the_limit_of_event_api_requests_is_reached(
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );

        $eventsApiDebugRepository->persist([
                'timestamp' => 1609459200,
                'level' => 'warning',
                'message' => 'The maximum number of events sent per hour has been reached.',
                'connection_code' => null,
                'context' => [],
            ])
            ->shouldBeCalled();

        $this->logLimitOfEventsApiRequestsReached();
    }

    private function createEvent(): EventInterface
    {
        $event = new class (
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        ) extends Event
        {
            public function getName(): string
            {
                return 'my_event';
            }
        };

        return $event;
    }
}
