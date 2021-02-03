<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectHourlyIntervalsToRefreshQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectHourlyIntervalsToRefreshQueryIntegration extends TestCase
{
    public function test_it_fetches_hourly_intervals_to_refresh(): void
    {
        // For the interval 10:00 -> 11:00, the event MUST be resfreshed.
        $this->getAuditLoader()->insert(
            new HourlyEventCount(
                'erp',
                HourlyInterval::createFromDateTime(
                    new \DateTimeImmutable('2020-01-01 10:59:59', new \DateTimeZone('UTC')),
                ),
                100,
                EventTypes::PRODUCT_UPDATED
            ),
            new \DateTimeImmutable('2020-01-01 10:45:00', new \DateTimeZone('UTC'))
        );

        // For the interval 11:00 -> 12:00, the event MUST NOT be resfreshed.
        $this->getAuditLoader()->insert(
            new HourlyEventCount(
                'erp',
                HourlyInterval::createFromDateTime(
                    new \DateTimeImmutable('2020-01-01 11:59:59', new \DateTimeZone('UTC')),
                ),
                100,
                EventTypes::PRODUCT_CREATED
            ),
            new \DateTimeImmutable('2020-01-01 12:00:00', new \DateTimeZone('UTC'))
        );

        // For the interval 12:00 -> 13:00, the first event MUST NOT be refreshed, but the second one MUST be refreshed.
        $this->getAuditLoader()->insert(
            new HourlyEventCount(
                'erp',
                HourlyInterval::createFromDateTime(
                    new \DateTimeImmutable('2020-01-01 12:59:59', new \DateTimeZone('UTC')),
                ),
                100,
                EventTypes::PRODUCT_CREATED
            ),
            new \DateTimeImmutable('2020-01-01 13:00:00', new \DateTimeZone('UTC'))
        );
        $this->getAuditLoader()->insert(
            new HourlyEventCount(
                'franklin',
                HourlyInterval::createFromDateTime(
                    new \DateTimeImmutable('2020-01-01 12:59:59', new \DateTimeZone('UTC')),
                ),
                100,
                EventTypes::PRODUCT_CREATED
            ),
            new \DateTimeImmutable('2020-01-01 12:45:00', new \DateTimeZone('UTC'))
        );

        $expectedResult = [
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:59:59', new \DateTimeZone('UTC')),
            ),
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 12:59:59', new \DateTimeZone('UTC')),
            )
        ];
        $result = $this->getSelectHourlyIntervalsToRefreshQuery()->execute();

        Assert::assertCount(2, $expectedResult);
        Assert::assertTrue($expectedResult[0]->equals($result[0]));
        Assert::assertTrue($expectedResult[1]->equals($result[1]));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getAuditLoader(): AuditLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.audit_loader');
    }

    private function getSelectHourlyIntervalsToRefreshQuery(): DbalSelectHourlyIntervalsToRefreshQuery
    {
        return $this->get('akeneo_connectivity_connection.persistence.query.select_hourly_intervals_to_refresh');
    }
}
