<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\SystemClock;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\SearchEventSubscriptionDebugLogsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\EventSubscriptionLogLoader;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchEventSubscriptionDebugLogsQueryIntegration extends TestCase
{
    private EventSubscriptionLogLoader $eventSubscriptionLogLoader;
    private SearchEventSubscriptionDebugLogsQuery $query;
    private FakeClock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(SearchEventSubscriptionDebugLogsQuery::class);
        $this->eventSubscriptionLogLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.event_subscription_log_loader'
        );
        $this->clock = $this->get(SystemClock::class);

        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }

    public function test_it_returns_the_correct_amount_of_logs_by_page()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->generateLogs(
            function () use ($timestamp) {
                return [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            30
        );

        $result = $this->query->execute('a_connection_code');

        Assert::assertCount(25, $result['results']);
        Assert::assertEquals(30, $result['total']);
    }

    public function test_it_returns_the_total_amount_of_notice_and_info_logs()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->generateLogs(
            function ($index) use ($timestamp) {
                return [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => $index % 2 ? EventsApiDebugLogLevels::NOTICE : EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            101
        );

        $count = 0;
        $searchAfter = null;

        do {
            $result = $this->query->execute('a_connection_code', $searchAfter);
            $count += \count($result['results']);
            $searchAfter = $result['search_after'];
        } while (\count($result['results']) === 25);

        Assert::assertEquals(100, $count);
        Assert::assertEquals(100, $result['total']);
    }

    public function test_it_returns_the_total_amount_of_logs_filtered_by_level()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->generateLogs(
            function ($index) use ($timestamp) {
                return [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            101
        );

        $count = 0;
        $searchAfter = null;
        $filters = [
            'levels' => [EventsApiDebugLogLevels::NOTICE]
        ];
        do {
            $result = $this->query->execute('a_connection_code', $searchAfter, $filters);
            $count += \count($result['results']);
            $searchAfter = $result['search_after'];
        } while (\count($result['results']) === 25);

        Assert::assertEquals(100, $count);
        Assert::assertEquals(100, $result['total']);
    }

    public function test_it_returns_the_last_warning_and_error_logs()
    {
        // The limit is 72 hours before now.
        $timestampLimit = $this->clock->now()->getTimestamp() - (72 * 60 * 60);
        $timestampOlderThanLimit = $timestampLimit - 60;
        $timestampNewerThanLimit = $timestampLimit + 60;

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampOlderThanLimit,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampNewerThanLimit,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampOlderThanLimit,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampNewerThanLimit,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $result = $this->query->execute('whatever');

        Assert::assertCount(2, $result['results']);
    }

    public function test_it_returns_logs_ordered_by_date_desc()
    {
        $timestampNow = $this->clock->now()->getTimestamp();

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampNow - 5,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampNow - 1,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampNow - 3,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampNow - 4,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampNow - 2,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
            ]
        );

        $logs = $this->query->execute('a_connection_code')['results'];

        Assert::assertEquals($timestampNow - 1, $logs[0]['timestamp']);
        Assert::assertEquals($timestampNow - 2, $logs[1]['timestamp']);
        Assert::assertEquals($timestampNow - 3, $logs[2]['timestamp']);
        Assert::assertEquals($timestampNow - 4, $logs[3]['timestamp']);
        Assert::assertEquals($timestampNow - 5, $logs[4]['timestamp']);
    }

    public function test_it_returns_logs_only_for_the_specified_connection()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'whatever',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $logs = $this->query->execute('a_connection_code')['results'];

        Assert::assertCount(2, $logs);

        foreach ($logs as $log) {
            Assert::assertContains($log['connection_code'], ['a_connection_code', null]);
        }
    }

    public function test_it_ignores_new_logs_when_using_search_after()
    {
        $firstTimestamp = $this->clock->now()->getTimestamp();
        $this->generateLogs(
            function () use ($firstTimestamp) {
                return [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $firstTimestamp,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            30
        );

        $firstPage = $this->query->execute('a_connection_code');
        Assert::assertCount(25, $firstPage['results']);

        $this->clock->setNow($this->clock->now()->modify('+30 seconds'));
        $secondTimestamp = $this->clock->now()->getTimestamp();
        $this->generateLogs(
            function () use ($secondTimestamp) {
                return [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $secondTimestamp,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            30
        );

        $secondPage = $this->query->execute('a_connection_code', $firstPage['search_after']);

        // At the time of the first query, there were 30 logs.
        // The first page returned 25 logs, and we want to be sure the second page returns the 5 next logs
        // and is not returning logs that were added between the requests.
        Assert::assertCount(5, $secondPage['results']);
        foreach ($secondPage['results'] as $log) {
            Assert::assertEquals($firstTimestamp, $log['timestamp']);
        }
    }

    public function test_it_filters_on_log_level()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => 'whatever',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
            ]
        );

        $filters = [
            'levels' => [EventsApiDebugLogLevels::NOTICE, EventsApiDebugLogLevels::INFO]
        ];

        $result = $this->query->execute('a_connection_code', null, $filters);

        \usort(
            $result['results'],
            function ($a, $b) {
                return \strcmp($a['level'], $b['level']);
            }
        );

        Assert::assertEquals(2, $result['total']);
        Assert::assertCount(2, $result['results']);
        Assert::assertEquals(EventsApiDebugLogLevels::INFO, $result['results'][0]['level']);
        Assert::assertEquals(EventsApiDebugLogLevels::NOTICE, $result['results'][1]['level']);
    }

    public function test_it_does_not_filter()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
            ]
        );

        $filters = [
            'levels' => [EventsApiDebugLogLevels::NOTICE, EventsApiDebugLogLevels::INFO, EventsApiDebugLogLevels::WARNING, EventsApiDebugLogLevels::ERROR]
        ];

        $result = $this->query->execute('a_connection_code', null, $filters);

        \usort(
            $result['results'],
            function ($a, $b) {
                return \strcmp($a['level'], $b['level']);
            }
        );

        Assert::assertEquals(5, $result['total']);
        Assert::assertCount(5, $result['results']);
    }

    public function test_it_filters_on_log_timestamp_from()
    {
        $timestampFrom = $this->clock->now()->getTimestamp();

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampFrom + 20,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampFrom,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'whatever',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampFrom,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampFrom - 20,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $filters = [
            'timestamp_from' => $timestampFrom,
        ];

        $result = $this->query->execute('a_connection_code', null, $filters);

        \usort(
            $result['results'],
            function ($a, $b) {
                if ($a['timestamp'] === $b['timestamp']) {
                    return 0;
                }

                return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
            }
        );

        Assert::assertEquals(2, $result['total']);
        Assert::assertCount(2, $result['results']);
        Assert::assertEquals($timestampFrom, $result['results'][0]['timestamp']);
        Assert::assertEquals(EventsApiDebugLogLevels::INFO, $result['results'][0]['level']);
        Assert::assertEquals($timestampFrom + 20, $result['results'][1]['timestamp']);
    }

    public function test_it_filters_on_log_timestamp_to()
    {
        $timestampTo = $this->clock->now()->getTimestamp();

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampTo + 20,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampTo,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampTo,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'whatever',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampTo - 20,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $filters = [
            'timestamp_to' => $timestampTo,
        ];

        $result = $this->query->execute('a_connection_code', null, $filters);

        \usort(
            $result['results'],
            function ($a, $b) {
                if ($a['timestamp'] === $b['timestamp']) {
                    return 0;
                }

                return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
            }
        );

        Assert::assertEquals(2, $result['total']);
        Assert::assertCount(2, $result['results']);
        Assert::assertEquals($timestampTo - 20, $result['results'][0]['timestamp']);
        Assert::assertEquals($timestampTo, $result['results'][1]['timestamp']);
        Assert::assertEquals(EventsApiDebugLogLevels::INFO, $result['results'][1]['level']);
    }

    public function test_it_searches_a_pattern_on_message()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;
        $firstTimestampToFind = $timestamp - 40;
        $secondTimestampToFind = $timestamp - 30;
        $anotherTimestamp = $timestamp - 20;

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $firstTimestampToFind,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Message a word to find',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $secondTimestampToFind,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'the messagE to finD',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'a message not found because the second word is missing',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $anotherTimestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'no word here',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $anotherTimestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => '',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
            ]
        );

        $filters = [
            'text' => 'SsaGe ind',
        ];

        $result = $this->query->execute('a_connection_code', null, $filters);

        $resultTimestamps = \array_map(
            function ($log) {
                return $log['timestamp'];
            },
            $result['results']
        );
        \sort($resultTimestamps);

        Assert::assertEquals(2, $result['total']);
        Assert::assertEquals($firstTimestampToFind, $resultTimestamps[0]);
        Assert::assertEquals($secondTimestampToFind, $resultTimestamps[1]);
    }

    public function test_it_searches_a_pattern_on_context()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;
        $firstTimestampToFind = $timestamp - 20;
        $secondTimestampToFind = $timestamp - 10;

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $firstTimestampToFind,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [
                        'event' => [
                            'action' => 'action to find',
                            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49c',
                            'event_datetime' => '1970-01-01T00:00:00+00:00',
                            'author' => 'julia',
                            'author_type' => 'ui',
                        ],
                    ],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $secondTimestampToFind,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [
                        'event_subscription_url' => 'event_subscription_url',
                        'events' => [
                            'uuid' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                            'author' => 'julia',
                            'author_type' => 'ui',
                            'name' => 'product.created',
                            'timestamp' => 1577836800,
                        ],
                        [
                            'uuid' => '8bdfe74c-da2e-4bda-a2b1-b5e2a3006ea3',
                            'author' => 'author to find',
                            'author_type' => 'ui',
                            'name' => 'product.updated',
                            'timestamp' => 1577836811,
                        ],
                    ],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [
                        'event' => [
                            'action' => 'product.created',
                            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                            'event_datetime' => '1970-01-01T00:00:00+00:00',
                            'author' => 'julia',
                            'author_type' => 'ui',
                        ],
                    ],
                ],
            ],
        );

        $filters = [
            'text' => 'to find',
        ];

        $result = $this->query->execute('a_connection_code', null, $filters);

        $resultTimestamps = \array_map(
            function ($log) {
                return $log['timestamp'];
            },
            $result['results']
        );
        \sort($resultTimestamps);

        Assert::assertEquals(2, $result['total']);
        Assert::assertEquals($firstTimestampToFind, $resultTimestamps[0]);
        Assert::assertEquals($secondTimestampToFind, $resultTimestamps[1]);
    }

    private function generateLogs(callable $generator, int $number): void
    {
        $this->insertLogs(\array_map($generator, \range(0, $number - 1)));
    }

    private function insertLogs(array $logs): void
    {
        $this->eventSubscriptionLogLoader->bulkInsert($logs);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
