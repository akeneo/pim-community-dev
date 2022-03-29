<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence\DbalUpsertEventCountQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class DbalUpsertEventCountQueryIntegration extends TestCase
{
    private DbalUpsertEventCountQuery $query;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(DbalUpsertEventCountQuery::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_many_hourly_event_count(): void
    {
        $event1 = new HourlyEventCount(
            'erp',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:59:59', new \DateTimeZone('UTC'))
            ),
            100,
            EventTypes::PRODUCT_CREATED
        );
        $event2 = new HourlyEventCount(
            'franklin',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 12:59:59', new \DateTimeZone('UTC'))
            ),
            500,
            EventTypes::PRODUCT_UPDATED
        );

        $this->query->execute($event1);
        $this->query->execute($event2);

        $sql = <<<SQL
SELECT connection_code, event_datetime, event_count, event_type, updated
FROM akeneo_connectivity_connection_audit_product
ORDER BY event_type, event_datetime
SQL;
        $eventCounts = $this->connection->fetchAllAssociative($sql);

        Assert::assertCount(2, $eventCounts);
        $this->assertEqualsHourlyEventCount($event1, $eventCounts[0]);
        $this->assertEqualsHourlyEventCount($event2, $eventCounts[1]);
    }

    public function test_it_adds_the_event_count_on_duplicate(): void
    {
        $event1 = new HourlyEventCount(
            'erp',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:59:59', new \DateTimeZone('UTC'))
            ),
            100,
            EventTypes::PRODUCT_CREATED
        );
        $this->query->execute($event1);

        $event2 = new HourlyEventCount(
            'erp',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:59:59', new \DateTimeZone('UTC'))
            ),
            200,
            EventTypes::PRODUCT_CREATED
        );
        $this->query->execute($event2);

        $sql = <<<SQL
SELECT connection_code, event_datetime, event_count, event_type, updated
FROM akeneo_connectivity_connection_audit_product
ORDER BY event_type, event_datetime
SQL;
        $eventCounts = $this->connection->fetchAllAssociative($sql);

        $expectedEvent = new HourlyEventCount(
            'erp',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:59:59', new \DateTimeZone('UTC'))
            ),
            300,
            EventTypes::PRODUCT_CREATED
        );

        Assert::assertCount(1, $eventCounts);
        $this->assertEqualsHourlyEventCount($expectedEvent, $eventCounts[0]);
    }

    private function assertEqualsHourlyEventCount(HourlyEventCount $expected, array $actual): void
    {
        Assert::assertEquals(
            $expected->connectionCode(),
            $actual['connection_code']
        );
        Assert::assertEquals(
            $expected->hourlyInterval()->fromDateTime()->format('Y-m-d H:i:s'),
            $actual['event_datetime']
        );
        Assert::assertEquals(
            $expected->eventCount(),
            (int) $actual['event_count']
        );
        Assert::assertEquals(
            $expected->eventType(),
            $actual['event_type']
        );
    }
}
