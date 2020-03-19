<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\AuditLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectConnectionsEventCountByDayQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsEventCountByDayQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var AuditLoader */
    private $auditLoader;

    /** @var SelectConnectionsEventCountByDayQuery */
    private $selectConnectionsEventCountByDayQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->auditLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_loader');
        $this->selectConnectionsEventCountByDayQuery = $this->get('akeneo_connectivity.connection.persistence.query.select_connections_event_count_by_day');
    }

    public function test_it_gets_data_for_connections_with_audit_data(): void
    {
        $this->connectionLoader->createConnection('sap', 'SAP', FlowType::DATA_SOURCE);
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::DATA_SOURCE);

        array_map(function (HourlyEventCount $hourlyEventCount) {
            $this->auditLoader->insert($hourlyEventCount);
        }, [
            new HourlyEventCount('sap', HourlyInterval::createFromDateTime(new \DateTime('2020-01-01 12:00:00', new \DateTimeZone('UTC'))), 5, EventTypes::PRODUCT_UPDATED),
            new HourlyEventCount(AllConnectionCode::CODE, HourlyInterval::createFromDateTime(new \DateTime('2020-01-01 23:00:00', new \DateTimeZone('UTC'))), 12, EventTypes::PRODUCT_UPDATED),
            // Expected results
            new HourlyEventCount('sap', HourlyInterval::createFromDateTime(new \DateTime('2020-01-02 00:00:00', new \DateTimeZone('UTC'))), 10, EventTypes::PRODUCT_UPDATED),
            new HourlyEventCount(AllConnectionCode::CODE, HourlyInterval::createFromDateTime(new \DateTime('2020-01-02 12:00:00', new \DateTimeZone('UTC'))), 8, EventTypes::PRODUCT_UPDATED),
            new HourlyEventCount('sap', HourlyInterval::createFromDateTime(new \DateTime('2020-01-03 23:00:00', new \DateTimeZone('UTC'))), 4, EventTypes::PRODUCT_UPDATED),
            // End of expected results
            new HourlyEventCount('bynder', HourlyInterval::createFromDateTime(new \DateTime('2020-01-04 00:00:00', new \DateTimeZone('UTC'))), 2, EventTypes::PRODUCT_UPDATED),
        ]);

        $result = $this->selectConnectionsEventCountByDayQuery->execute(
            EventTypes::PRODUCT_UPDATED,
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-03 23:00:00', new \DateTimeZone('UTC')),
        );

        $expectedResult = [
            'bynder' => [],
            'sap' => [
                [new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')), 10],
                [new \DateTimeImmutable('2020-01-03 23:00:00', new \DateTimeZone('UTC')), 4]
            ],
            AllConnectionCode::CODE => [
                [new \DateTimeImmutable('2020-01-02 12:00:00', new \DateTimeZone('UTC')), 8]
            ]
        ];

        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_gets_data_for_connections_without_audit_data(): void
    {
        $this->connectionLoader->createConnection('sap', 'SAP', FlowType::DATA_SOURCE);

        $result = $this->selectConnectionsEventCountByDayQuery->execute(
            EventTypes::PRODUCT_UPDATED,
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-08 00:00:00', new \DateTimeZone('UTC')),
        );

        $expectedResult = [
            'sap' => [],
            AllConnectionCode::CODE => []
        ];

        Assert::assertSame($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
