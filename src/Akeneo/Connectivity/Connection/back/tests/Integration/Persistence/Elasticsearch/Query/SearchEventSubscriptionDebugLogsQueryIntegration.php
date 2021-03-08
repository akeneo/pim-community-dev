<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\SearchEventSubscriptionDebugLogsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchEventSubscriptionDebugLogsQueryIntegration extends TestCase
{
    private Client $elasticsearchClient;
    private SearchEventSubscriptionDebugLogsQuery $query;
    private FakeClock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(
            'akeneo_connectivity.connection.persistence.query.search_event_subscription_debug_logs_query'
        );
        $this->elasticsearchClient = $this->get('akeneo_connectivity.client.events_api_debug');
        $this->clock = $this->get('akeneo_connectivity.connection.system_clock');

        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }

    public function test_it_returns_the_correct_amount_of_logs_by_page()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->generateLogs(
            function ($index) use ($timestamp) {
                return [
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
            $count += count($result['results']);
            $searchAfter = $result['search_after'];
        } while (count($result['results']) === 25);

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
                    'timestamp' => $timestampOlderThanLimit,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNewerThanLimit,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampOlderThanLimit,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
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
                    'timestamp' => $timestampNow - 5,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 1,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 3,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 4,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
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
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'whatever',
                    'context' => [],
                ],
                [
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
            function ($index) use ($firstTimestamp) {
                return [
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
            function ($index) use ($secondTimestamp) {
                return [
                    'timestamp' => $secondTimestamp,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            30
        );

        $secondPage = $this->query->execute('a_connection_code', $firstPage['search_after'],);

        // At the time of the first query, there were 30 logs.
        // The first page returned 25 logs, and we want to be sure the second page returns the 5 next logs
        // and is not returning logs that were added between the requests.
        Assert::assertCount(5, $secondPage['results']);
        foreach($secondPage['results'] as $log) {
            Assert::assertEquals($firstTimestamp, $log['timestamp']);
        }
    }

    public function test_it_filters_on_log_level()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->insertLogs(
            [
                [
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::WARNING,
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => 'whatever',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::ERROR,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $filters = [
            'levels' => [EventsApiDebugLogLevels::NOTICE, EventsApiDebugLogLevels::INFO]
        ];

        $result = $this->query->execute('a_connection_code', null, $filters);

        // TODO: assert notice et info
        Assert::assertCount(3, $result['results']);
        Assert::assertEquals(3, $result['total']);
    }

    private function generateLogs(callable $generator, int $number): void
    {
        $this->insertLogs(array_map($generator, range(0, $number - 1)));
    }

    private function insertLogs(array $logs): void
    {
        $this->elasticsearchClient->bulkIndexes($logs);
        $this->elasticsearchClient->refreshIndex();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
