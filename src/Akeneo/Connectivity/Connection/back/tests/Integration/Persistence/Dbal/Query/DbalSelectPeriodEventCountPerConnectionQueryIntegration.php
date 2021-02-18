<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectPeriodEventCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectPeriodEventCountPerConnectionQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var AuditLoader */
    private $auditLoader;

    /** @var SelectPeriodEventCountPerConnectionQuery */
    private $selectPeriodEventCountPerConnectionQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->auditLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_loader');
        $this->selectPeriodEventCountPerConnectionQuery = $this->get('akeneo_connectivity.connection.persistence.query.select_period_event_count_per_connection');
    }

    public function test_it_gets_data_for_connections_with_audit_data(): void
    {
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);

        $this->createHourlyEventCounts([
            ['sap', EventTypes::PRODUCT_UPDATED, '2020-01-01 12:00:00', 5],
            // Begin  of requested period interval
            ['bynder', EventTypes::PRODUCT_UPDATED, '2020-01-02 00:00:00', 2],
            ['sap', EventTypes::PRODUCT_UPDATED, '2020-01-02 00:00:00', 10],
            ['sap', EventTypes::PRODUCT_UPDATED, '2020-01-03 23:00:00', 4],
            ['bynder', EventTypes::PRODUCT_UPDATED, '2020-01-04 00:00:00', 2],
            // End of requested period interval
            ['bynder', EventTypes::PRODUCT_UPDATED, '2020-01-05 00:00:00', 12],
        ]);

        $period = new DateTimePeriod(
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-04 00:00:00', new \DateTimeZone('UTC'))
        );
        $result = $this->selectPeriodEventCountPerConnectionQuery->execute(
            EventTypes::PRODUCT_UPDATED,
            $period
        );

        $expectedResult = [
            new PeriodEventCount('<all>', $period->start(), $period->end(), [
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')), 12),
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-03 23:00:00', new \DateTimeZone('UTC')), 4),
            ]),
            new PeriodEventCount('bynder', $period->start(), $period->end(), [
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')), 2),
            ]),
            new PeriodEventCount('sap', $period->start(), $period->end(), [
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')), 10),
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-03 23:00:00', new \DateTimeZone('UTC')), 4),
            ]),
        ];

        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_gets_data_for_connections_without_audit_data(): void
    {
        $this->connectionLoader->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);

        $period = new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-08 00:00:00', new \DateTimeZone('UTC'))
        );
        $result = $this->selectPeriodEventCountPerConnectionQuery->execute(
            EventTypes::PRODUCT_UPDATED,
            $period
        );

        $expectedResult = [
            new PeriodEventCount('<all>', $period->start(), $period->end(), []),
            new PeriodEventCount('sap', $period->start(), $period->end(), []),
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
