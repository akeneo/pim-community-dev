<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\GetEventSubscriptionLogsQuery;
use Akeneo\Connectivity\Connection\Tests\Integration\FakeClock;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEventSubscriptionLogsQueryIntegration extends TestCase
{
    private Client $elasticsearchClient;
    private GetEventSubscriptionLogsQuery $getEventSubscriptionLogsQuery;
    private FakeClock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getEventSubscriptionLogsQuery = $this->get(
            'akeneo_connectivity.connection.persistence.query.get_event_subscription_logs_query'
        );
        $this->elasticsearchClient = $this->get('akeneo_connectivity.client.events_api_debug');
        $this->clock = $this->get('akeneo_connectivity.connection.clock');

        // timestamp = 1612326611
        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }

    public function test_it_returns_the_correct_amount_of_notice_and_info_logs()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->generateLogs(
            function ($index) use ($timestamp) {
                return [
                    'timestamp' => $timestamp,
                    'level' => $index % 2 ? 'notice' : 'info',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            101
        );

        $result = $this->getEventSubscriptionLogsQuery->execute('a_connection_code');

        Assert::assertEquals(100, iterator_count($result));
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
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNewerThanLimit,
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampOlderThanLimit,
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNewerThanLimit,
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $result = $this->getEventSubscriptionLogsQuery->execute('whatever');

        Assert::assertEquals(2, iterator_count($result));
    }

    public function test_it_returns_logs_ordered_by_date_asc()
    {
        $timestampNow = $this->clock->now()->getTimestamp();

        $this->insertLogs(
            [
                [
                    'timestamp' => $timestampNow - 5,
                    'level' => 'notice',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 1,
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 3,
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 4,
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 2,
                    'level' => 'notice',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
            ]
        );

        $logs = iterator_to_array($this->getEventSubscriptionLogsQuery->execute('a_connection_code'));

        Assert::assertEquals($timestampNow - 5, $logs[0]['timestamp']);
        Assert::assertEquals($timestampNow - 4, $logs[1]['timestamp']);
        Assert::assertEquals($timestampNow - 3, $logs[2]['timestamp']);
        Assert::assertEquals($timestampNow - 2, $logs[3]['timestamp']);
        Assert::assertEquals($timestampNow - 1, $logs[4]['timestamp']);
    }

    public function test_it_returns_logs_only_for_the_specified_connection()
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->insertLogs(
            [
                [
                    'timestamp' => $timestamp,
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestamp,
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => 'whatever',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestamp,
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $logs = iterator_to_array($this->getEventSubscriptionLogsQuery->execute('a_connection_code'));

        Assert::assertEquals(2, count($logs));

        foreach ($logs as $log) {
            Assert::assertContains($log['connection_code'], ['a_connection_code', null]);
        }
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

    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}

