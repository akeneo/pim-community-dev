<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\DailyEventCount;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository\DbalConnectionRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository\DbalEventCountRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

class DbalEventCountRepositoryIntegration extends TestCase
{
    /** @var DbalConnection */
    private $dbalConnection;

    /** @var DbalEventCountRepository */
    private $repository;

    /** @var ConnectionLoader */
    private $connectionLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->repository = $this->get('akeneo_connectivity.connection.persistence.repository.event_count');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    public function test_it_creates_many_daily_event_count()
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $event1 = new DailyEventCount('magento', '2020-01-13', 13, EventTypes::PRODUCT_CREATED);
        $event2 = new DailyEventCount('magento', '2020-01-13', 23, EventTypes::PRODUCT_UPDATED);
        $event3 = new DailyEventCount('magento', '2020-01-14', 15, EventTypes::PRODUCT_CREATED);
        $event4 = new DailyEventCount('magento', '2020-01-14', 2, EventTypes::PRODUCT_UPDATED);
        $this->repository->bulkInsert([$event1, $event2, $event3, $event4]);

        $sqlQuery = <<<SQL
SELECT connection_code, event_date, event_count, event_type, updated 
FROM akeneo_connectivity_connection_audit ORDER BY event_type, event_date
SQL;
        $eventCounts = $this->dbalConnection->fetchAll($sqlQuery);
        Assert::assertCount(4, $eventCounts);

        $this->assertDailyEventCount($eventCounts[0], 'magento', '2020-01-13', 13, EventTypes::PRODUCT_CREATED);
        $this->assertDailyEventCount($eventCounts[1], 'magento', '2020-01-14', 15, EventTypes::PRODUCT_CREATED);
        $this->assertDailyEventCount($eventCounts[2], 'magento', '2020-01-13', 23, EventTypes::PRODUCT_UPDATED);
        $this->assertDailyEventCount($eventCounts[3], 'magento', '2020-01-14', 2, EventTypes::PRODUCT_UPDATED);
    }

    public function test_it_updates_the_event_count_and_updated_date_on_duplicate()
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $event1 = new DailyEventCount('magento', '2020-01-13', 13, EventTypes::PRODUCT_CREATED);
        $event2 = new DailyEventCount('magento', '2020-01-13', 23, EventTypes::PRODUCT_UPDATED);
        $this->repository->bulkInsert([$event1, $event2]);

        $updatedEvent1 = new DailyEventCount('magento', '2020-01-13', 18, EventTypes::PRODUCT_CREATED);
        $this->repository->bulkInsert([$updatedEvent1]);

        $sqlQuery = <<<SQL
SELECT connection_code, event_date, event_count, event_type, updated 
FROM akeneo_connectivity_connection_audit ORDER BY event_type, event_date
SQL;
        $eventCounts = $this->dbalConnection->fetchAll($sqlQuery);
        Assert::assertCount(2, $eventCounts);
    }

    private function assertDailyEventCount(
        array $eventCountRow,
        string $connectionCode,
        string $eventDate,
        int $eventCount,
        string $eventType
    ): void {
        Assert::assertEquals($connectionCode, $eventCountRow['connection_code']);
        Assert::assertEquals($eventDate, $eventCountRow['event_date']);
        Assert::assertEquals($eventCount, (int) $eventCountRow['event_count']);
        Assert::assertEquals($eventType, $eventCountRow['event_type']);

        $updatedDate = date_create_from_format('Y-m-d H:i:s', $eventCountRow['updated'])->format('Y-m-d');
        Assert::assertEquals(date('Y-m-d'), $updatedDate);
    }

    // TODO: test on duplicate key update

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function selectConnectionFromDb(string $code): array
    {
        $query = <<<SQL
    SELECT code, label, flow_type, client_id, user_id, image
    FROM akeneo_connectivity_connection
    WHERE code = :code
SQL;
        $statement = $this->dbalConnection->executeQuery($query, ['code' => $code]);

        return $statement->fetch();
    }
}
