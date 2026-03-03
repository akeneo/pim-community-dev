<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\SystemClock;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\GetAllEventSubscriptionDebugLogsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllEventSubscriptionDebugLogsQueryIntegration extends TestCase
{
    private Client $elasticsearchClient;
    private GetAllEventSubscriptionDebugLogsQuery $getEventSubscriptionLogsQuery;
    private FakeClock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getEventSubscriptionLogsQuery = $this->get(GetAllEventSubscriptionDebugLogsQuery::class);
        $this->elasticsearchClient = $this->get('akeneo_connectivity.client.events_api_debug');
        $this->clock = $this->get(SystemClock::class);

        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }

    public function test_it_returns_the_correct_amount_of_notice_and_info_logs(): void
    {
        $timestamp = $this->clock->now()->getTimestamp() - 10;

        $this->generateLogs(
            fn ($index): array => [
                'id' => Uuid::uuid4()->toString(),
                'timestamp' => $timestamp,
                'level' => $index % 2 !== 0 ? EventsApiDebugLogLevels::NOTICE : EventsApiDebugLogLevels::INFO,
                'message' => 'Foo bar',
                'connection_code' => null,
                'context' => [],
            ],
            101
        );

        $result = $this->getEventSubscriptionLogsQuery->execute('a_connection_code');

        Assert::assertEquals(100, \iterator_count($result));
    }

    public function test_it_returns_only_the_newest_notice_and_info_logs(): void
    {
        $timestampNow = $this->clock->now()->getTimestamp() - 10;
        $timestampStep = 10;
        $countOfGeneratedLogs = 101;
        $excludeTimestamp = $timestampNow - $timestampStep * $countOfGeneratedLogs;

        $this->generateLogs(
            function ($index) use (&$timestampNow, $timestampStep): array {
                $timestampNow -= $timestampStep;

                return [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $timestampNow,
                    'level' => $index % 2 !== 0 ? EventsApiDebugLogLevels::NOTICE : EventsApiDebugLogLevels::INFO,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            $countOfGeneratedLogs
        );

        $logs = \iterator_to_array($this->getEventSubscriptionLogsQuery->execute('a_connection_code'));

        $timestamps = \array_map(
            fn ($log): int => $log['timestamp'],
            $logs
        );

        Assert::assertNotContains($excludeTimestamp, $timestamps);
    }

    public function test_it_returns_the_last_warning_and_error_logs(): void
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

        $result = $this->getEventSubscriptionLogsQuery->execute('whatever');

        Assert::assertEquals(2, \iterator_count($result));
    }

    public function test_it_returns_logs_ordered_by_date_asc(): void
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

        $logs = \iterator_to_array($this->getEventSubscriptionLogsQuery->execute('a_connection_code'));

        Assert::assertEquals($timestampNow - 5, $logs[0]['timestamp']);
        Assert::assertEquals($timestampNow - 4, $logs[1]['timestamp']);
        Assert::assertEquals($timestampNow - 3, $logs[2]['timestamp']);
        Assert::assertEquals($timestampNow - 2, $logs[3]['timestamp']);
        Assert::assertEquals($timestampNow - 1, $logs[4]['timestamp']);
    }

    public function test_it_returns_logs_only_for_the_specified_connection(): void
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

        $logs = \iterator_to_array($this->getEventSubscriptionLogsQuery->execute('a_connection_code'));

        Assert::assertEquals(2, \count($logs));

        foreach ($logs as $log) {
            Assert::assertContains($log['connection_code'], ['a_connection_code', null]);
        }
    }

    private function generateLogs(callable $generator, int $number): void
    {
        $this->insertLogs(\array_map($generator, \range(0, $number - 1)));
    }

    private function insertLogs(array $logs): void
    {
        $this->elasticsearchClient->bulkIndexes($logs);
        $this->elasticsearchClient->refreshIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
