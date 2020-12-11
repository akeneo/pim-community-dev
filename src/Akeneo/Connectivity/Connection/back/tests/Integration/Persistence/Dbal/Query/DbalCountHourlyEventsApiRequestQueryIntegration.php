<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\EventsApiRequestCountLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\CountHourlyEventsApiRequestQuery;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalCountHourlyEventsApiRequestQueryIntegration extends TestCase
{
    private EventsApiRequestCountLoader $eventsApiRequestCountLoader;
    private CountHourlyEventsApiRequestQuery $countHourlyEventsApiRequestQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventsApiRequestCountLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.events_api_request_count_loader'
        );
        $this->countHourlyEventsApiRequestQuery = $this->get(
            'akeneo_connectivity.connection.persistence.query.count_hourly_events_api_request'
        );
    }

    public function test_it_counts_events_api_request_triggered_the_same_hour()
    {
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2020-01-02 10:10:41', new \DateTimeZone('UTC')),
            4
        );
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2020-01-02 11:22:31', new \DateTimeZone('UTC')),
            8
        );
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2020-01-02 11:59:59', new \DateTimeZone('UTC')),
            16
        );

        Assert::assertEquals(
            24,
            $this->countHourlyEventsApiRequestQuery->execute(
                new \DateTimeImmutable('2020-01-02 12:00:00', new \DateTimeZone('UTC'))
            )
        );
    }

    public function test_it_does_not_count_events_api_request_triggered_the_same_hour_but_not_the_same_day()
    {
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2020-02-03 09:07:44', new \DateTimeZone('UTC')),
            4
        );
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2020-02-03 10:34:00', new \DateTimeZone('UTC')),
            8
        );
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2020-02-04 10:42:11', new \DateTimeZone('UTC')),
            16
        );

        Assert::assertEquals(
            8,
            $this->countHourlyEventsApiRequestQuery->execute(
                new \DateTimeImmutable('2020-02-03 11:00:00', new \DateTimeZone('UTC'))
            )
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
