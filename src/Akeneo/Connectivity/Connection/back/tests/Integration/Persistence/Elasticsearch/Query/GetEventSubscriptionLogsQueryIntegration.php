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
        dump(iterator_to_array($result));

        Assert::assertEquals(iterator_count($result), 100);
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

        Assert::assertEquals(iterator_count($result), 2);
    }

    public function test_it_returns_logs_ordered_by_date_asc()
    {
        $timestampNow = $this->clock->now()->getTimestamp();

        $this->insertLogs(
            [
                [
                    'timestamp' => $timestampNow - 10,
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 30,
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $timestampNow - 20,
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
            ]
        );

        $logs = iterator_to_array($this->getEventSubscriptionLogsQuery->execute('a_connection_code'));

        Assert::assertEquals($logs[0]['timestamp'], $timestampNow - 30);
        Assert::assertEquals($logs[1]['timestamp'], $timestampNow - 20);
        Assert::assertEquals($logs[2]['timestamp'], $timestampNow - 10);
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

        Assert::assertEquals(count($logs), 2);

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

