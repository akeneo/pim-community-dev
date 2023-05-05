<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\ApiEventBuildErrorLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiDebugLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventSubscriptionSkippedOwnEventLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\LimitOfEventsApiRequestsReachedLoggerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
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
        EventsApiDebugRepositoryInterface $eventsApiDebugRepository
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

    public function it_logs_when_the_event_subscription_has_been_sent(
        EventsApiDebugRepositoryInterface $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );
        $this->shouldImplement(EventSubscriptionSkippedOwnEventLoggerInterface::class);

        $eventsApiDebugRepository->persist(
            Argument::that(function ($actual): bool {
                if (!isset($actual['id']) || !\is_string($actual['id'])) {
                    return false;
                }
                unset($actual['id']);
                return $actual === [
                        'timestamp' => 1609459200,
                        'level' => 'info',
                        'message' => 'The API event request was sent.',
                        'connection_code' => 'erp_000',
                        'context' => [
                            'event_subscription_url' => 'http://my-url.com',
                            'status_code' => 200,
                            'headers' => [],
                            'events' => [
                                [
                                    "action" => "my_event",
                                    "event_id" => "9979c367-595d-42ad-9070-05f62f31f49b",
                                    "event_datetime" => "1970-01-01T00:00:00+00:00",
                                    "author" => "julia",
                                    "author_type" => "ui",
                                ],
                            ],
                        ],
                    ];
            })
        )->shouldBeCalled();

        $this->logEventsApiRequestSucceed(
            'erp_000',
            [$this->createWebhookEvent()],
            'http://my-url.com',
            200,
            []
        );
    }

    public function it_logs_when_the_event_subscription_skipped_its_own_event(
        EventsApiDebugRepositoryInterface $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );
        $this->shouldImplement(EventSubscriptionSkippedOwnEventLoggerInterface::class);

        $eventsApiDebugRepository->persist(
            Argument::that(function ($actual): bool {
                if (!isset($actual['id']) || !\is_string($actual['id'])) {
                    return false;
                }
                unset($actual['id']);
                return $actual === [
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
                            ],
                        ],
                    ];
            })
        )->shouldBeCalled();

        $this->logEventSubscriptionSkippedOwnEvent('erp_000', $this->createEvent());
    }

    public function it_logs_when_the_limit_of_event_api_requests_is_reached(
        EventsApiDebugRepositoryInterface $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );
        $this->shouldImplement(LimitOfEventsApiRequestsReachedLoggerInterface::class);

        $eventsApiDebugRepository->persist(
            Argument::that(function ($actual): bool {
                if (!isset($actual['id']) || !\is_string($actual['id'])) {
                    return false;
                }
                unset($actual['id']);
                return $actual === [
                        'timestamp' => 1609459200,
                        'level' => 'warning',
                        'message' => 'The maximum number of events sent per hour has been reached.',
                        'connection_code' => null,
                        'context' => [],
                    ];
            })
        )->shouldBeCalled();

        $this->logLimitOfEventsApiRequestsReached();
    }

    public function it_logs_when_the_resource_was_not_found_or_access_denied(
        EventsApiDebugRepositoryInterface $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );
        $this->shouldImplement(ApiEventBuildErrorLoggerInterface::class);
        $eventsApiDebugRepository->persist(
            Argument::that(function ($actual): bool {
                if (!isset($actual['id']) || !\is_string($actual['id'])) {
                    return false;
                }
                unset($actual['id']);
                return $actual === [
                        'timestamp' => 1609459200,
                        'level' => 'notice',
                        'message' => 'The event was not sent because the product does not exists or the connection does not have the required permissions.',
                        'connection_code' => 'erp_000',
                        'context' => [
                            'event' => [
                                'action' => 'my_event',
                                'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                                'event_datetime' => '1970-01-01T00:00:00+00:00',
                                'author' => 'julia',
                                'author_type' => 'ui',
                            ],
                        ],
                    ];
            })
        )->shouldBeCalled();

        $this->logResourceNotFoundOrAccessDenied('erp_000', $this->createEvent());
    }

    private function createEvent(): EventInterface
    {
        return new class(Author::fromNameAndType('julia', Author::TYPE_UI), [], 0, '9979c367-595d-42ad-9070-05f62f31f49b') extends Event {
            public function getName(): string
            {
                return 'my_event';
            }
        };
    }

    private function createWebhookEvent(): WebhookEvent
    {
        return new WebhookEvent(
            'product.created',
            '9979c367-595d-42ad-9070-05f62f31f49b',
            '1970-01-01T00:00:00+00:00',
            Author::fromNameAndType('julia', Author::TYPE_UI),
            'ui',
            [],
            $this->createEvent()
        );
    }
}
