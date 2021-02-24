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

        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11.012345Z'));
    }

    public function test_it_returns_the_correct_amount_of_notice_and_info_logs()
    {
        $now = new \DateTime();

        $this->generateLogs(
            function ($index) use ($now) {
                return [
                    'timestamp' => $now->getTimestamp(),
                    'level' => $index % 2 ? 'notice' : 'info',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            101
        );

        $result = $this->getEventSubscriptionLogsQuery->execute('whatever');

        Assert::assertEquals(iterator_count($result), 100);
    }

    public function test_it_returns_the_last_warning_and_error_logs()
    {
        $limit = (clone $this->clock->now())->modify('-72 hours');
        $beforeLimit = (clone $limit)->modify('-1 minute');
        $afterLimit = (clone $limit)->modify('+1 minute');

        $this->insertLogs(
            [
                [
                    'timestamp' => $beforeLimit->getTimestamp(),
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $afterLimit->getTimestamp(),
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $beforeLimit->getTimestamp(),
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $afterLimit->getTimestamp(),
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
        $first = (clone $this->clock->now())->modify('-9 minutes');
        $third = (clone $this->clock->now())->modify('-3 minutes');
        $second = (clone $this->clock->now())->modify('-6 minutes');

        $this->insertLogs(
            [
                [
                    'timestamp' => $first->getTimestamp(),
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $third->getTimestamp(),
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
                [
                    'timestamp' => $second->getTimestamp(),
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $logs = iterator_to_array($this->getEventSubscriptionLogsQuery->execute('whatever'));

        Assert::assertEquals($logs[0]['timestamp'], $first->getTimestamp());
        Assert::assertEquals($logs[1]['timestamp'], $second->getTimestamp());
        Assert::assertEquals($logs[2]['timestamp'], $third->getTimestamp());
    }

    public function test_it_returns_logs_only_for_the_specified_connection()
    {
        $this->insertLogs(
            [
                [
                    'timestamp' => $this->clock->now()->getTimestamp(),
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $this->clock->now()->getTimestamp(),
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => 'whatever',
                    'context' => [],
                ],
                [
                    'timestamp' => $this->clock->now()->getTimestamp(),
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => 'a_connection_code',
                    'context' => [],
                ],
                [
                    'timestamp' => $this->clock->now()->getTimestamp(),
                    'level' => 'error',
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ],
            ]
        );

        $logs = iterator_to_array($this->getEventSubscriptionLogsQuery->execute('a_connection_code'));

        Assert::assertEquals(count($logs), 2);
        Assert::assertEquals($logs[0]['connection_code'], 'a_connection_code');
        Assert::assertEquals($logs[1]['connection_code'], 'a_connection_code');
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

