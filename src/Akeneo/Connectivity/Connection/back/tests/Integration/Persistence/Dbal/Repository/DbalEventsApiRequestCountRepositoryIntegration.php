<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiRequestCountRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalEventsApiRequestCountRepositoryIntegration extends TestCase
{
    public function test_it_creates_event_api_requests_count()
    {
        $expectedEventApiRequestCounts = [
            ['event_minute' => '1', 'event_count' => '6', 'updated' => '2020-12-09 17:01:00'],
            ['event_minute' => '2', 'event_count' => '12', 'updated' => '2020-12-09 17:02:00'],
            ['event_minute' => '3', 'event_count' => '24', 'updated' => '2020-12-09 18:03:00'],
        ];

        $this->getEventsApiRequestCountRepository()->upsert(
            new \DateTimeImmutable('2020-12-09 17:01:00', new \DateTimeZone('UTC')),
            6
        );

        $this->getEventsApiRequestCountRepository()->upsert(
            new \DateTimeImmutable('2020-12-09 17:02:00', new \DateTimeZone('UTC')),
            12
        );

        $this->getEventsApiRequestCountRepository()->upsert(
            new \DateTimeImmutable('2020-12-09 18:03:00', new \DateTimeZone('UTC')),
            24
        );

        $eventApiRequestCounts = $this->getEventApiRequestCounts();

        Assert::assertCount(3, $eventApiRequestCounts);
        $this->assertEqualsEventApiRequestCount($expectedEventApiRequestCounts[0], $eventApiRequestCounts[0]);
        $this->assertEqualsEventApiRequestCount($expectedEventApiRequestCounts[1], $eventApiRequestCounts[1]);
        $this->assertEqualsEventApiRequestCount($expectedEventApiRequestCounts[2], $eventApiRequestCounts[2]);
    }

    public function test_it_updates_event_api_requests_count_on_duplicate(): void
    {
        $expectedEventApiRequestCounts = [
            ['event_minute' => '1', 'event_count' => '6', 'updated' => '2020-12-09 17:01:00'],
            ['event_minute' => '2', 'event_count' => '30', 'updated' => '2020-12-09 17:02:59'],
            ['event_minute' => '3', 'event_count' => '48', 'updated' => '2020-12-11 18:03:12'],
        ];

        $this->getEventsApiRequestCountRepository()->upsert(
            new \DateTimeImmutable('2020-12-09 17:01:00', new \DateTimeZone('UTC')),
            6
        );

        $this->getEventsApiRequestCountRepository()->upsert(
            new \DateTimeImmutable('2020-12-09 17:02:12', new \DateTimeZone('UTC')),
            12
        );

        $this->getEventsApiRequestCountRepository()->upsert(
            new \DateTimeImmutable('2020-12-09 17:02:59', new \DateTimeZone('UTC')),
            18
        );

        $this->getEventsApiRequestCountRepository()->upsert(
            new \DateTimeImmutable('2020-12-09 18:03:00', new \DateTimeZone('UTC')),
            24
        );

        $this->getEventsApiRequestCountRepository()->upsert(
            new \DateTimeImmutable('2020-12-11 18:03:12', new \DateTimeZone('UTC')),
            24
        );

        $eventApiRequestCounts = $this->getEventApiRequestCounts();

        Assert::assertCount(3, $eventApiRequestCounts);
        $this->assertEqualsEventApiRequestCount($expectedEventApiRequestCounts[0], $eventApiRequestCounts[0]);
        $this->assertEqualsEventApiRequestCount($expectedEventApiRequestCounts[1], $eventApiRequestCounts[1]);
        $this->assertEqualsEventApiRequestCount($expectedEventApiRequestCounts[2], $eventApiRequestCounts[2]);
    }

    private function getEventApiRequestCounts(): array
    {
        $sql = <<<SQL
SELECT event_minute, event_count, updated
FROM akeneo_connectivity_connection_events_api_request_count
ORDER BY event_minute
SQL;

        return $this->getDbalConnection()->fetchAll($sql);
    }

    private function assertEqualsEventApiRequestCount(array $expected, array $actual): void
    {
        Assert::assertEquals($expected['event_minute'], $actual['event_minute']);
        Assert::assertEquals($expected['event_count'], $actual['event_count']);
        Assert::assertEquals($expected['updated'], $actual['updated']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getDbalConnection(): DbalConnection
    {
        return $this->get('database_connection');
    }

    private function getEventsApiRequestCountRepository(): EventsApiRequestCountRepository
    {
        return $this->get('akeneo_connectivity.connection.persistence.repository.events_api_request_count');
    }
}
