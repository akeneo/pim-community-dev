<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\AuditLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectPeriodEventCountsQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectPeriodEventCountsQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var AuditLoader */
    private $auditLoader;

    /** @var SelectPeriodEventCountsQuery */
    private $selectPeriodEventCountsQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->auditLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_loader');
        $this->selectPeriodEventCountsQuery = $this->get('akeneo_connectivity.connection.persistence.query.select_period_event_counts');
    }

    public function test_it_gets_data_for_connections_with_audit_data(): void
    {
        $this->connectionLoader->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::DATA_SOURCE, true);

        $this->createHourlyEventCounts([
            ['sap', EventTypes::PRODUCT_UPDATED, '2020-01-01 12:00:00', 5],
            [AllConnectionCode::CODE, EventTypes::PRODUCT_UPDATED, '2020-01-01 23:00:00', 12],
            // Expected results
            ['sap', EventTypes::PRODUCT_UPDATED, '2020-01-02 00:00:00', 10],
            [AllConnectionCode::CODE, EventTypes::PRODUCT_UPDATED, '2020-01-02 12:00:00', 8],
            ['sap', EventTypes::PRODUCT_UPDATED, '2020-01-03 23:00:00', 4],
            // End of expected results
            ['bynder', EventTypes::PRODUCT_UPDATED, '2020-01-04 00:00:00', 2],
        ]);

        $fromDateTime = new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'));
        $upToDateTime = new \DateTimeImmutable('2020-01-04 00:00:00', new \DateTimeZone('UTC'));
        $result = $this->selectPeriodEventCountsQuery->execute(
            EventTypes::PRODUCT_UPDATED,
            $fromDateTime,
            $upToDateTime,
        );

        $expectedResult = [
            new PeriodEventCount('bynder', $fromDateTime, $upToDateTime, []),
            new PeriodEventCount('sap', $fromDateTime, $upToDateTime, [
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')), 10),
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-03 23:00:00', new \DateTimeZone('UTC')), 4)
            ]),
            new PeriodEventCount('<all>', $fromDateTime, $upToDateTime, [
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-02 12:00:00', new \DateTimeZone('UTC')), 8)
            ])
        ];

        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_gets_data_for_connections_without_audit_data(): void
    {
        $this->connectionLoader->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);

        $fromDateTime = new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC'));
        $upToDateTime = new \DateTimeImmutable('2020-01-08 00:00:00', new \DateTimeZone('UTC'));
        $result = $this->selectPeriodEventCountsQuery->execute(
            EventTypes::PRODUCT_UPDATED,
            $fromDateTime,
            $upToDateTime,
        );

        $expectedResult = [
            new PeriodEventCount('sap', $fromDateTime, $upToDateTime, []),
            new PeriodEventCount('<all>', $fromDateTime, $upToDateTime, []),
        ];

        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createHourlyEventCounts(array $hourlyEventCountData): void
    {
        foreach ($hourlyEventCountData as [$connectionCode, $eventType, $dateTimeStr, $eventCount]) {
            $utcDateTime = (new \DateTimeImmutable($dateTimeStr, new \DateTimeZone('UTC')));

            $hourlyEventCount = new Write\HourlyEventCount(
                $connectionCode,
                HourlyInterval::createFromDateTime($utcDateTime),
                $eventCount,
                $eventType
            );

            $this->auditLoader->insert($hourlyEventCount);
        }
    }
}
