<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\EventsApiRequestCountLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectEventsApiRequestCountWithinLastHourQuery;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelectEventsApiRequestCountWithinLastHourQueryIntegration extends TestCase
{
    private EventsApiRequestCountLoader $eventsApiRequestCountLoader;
    private SelectEventsApiRequestCountWithinLastHourQuery $eventsApiRequestCountWithinLastHour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventsApiRequestCountLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.events_api_request_count_loader'
        );
        $this->eventsApiRequestCountWithinLastHour = $this->get(
            'akeneo_connectivity.connection.persistence.query.select_events_api_request_count_within_last_hour_query'
        );
    }

    public function test1()
    {
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2021-01-02 10:22:31', new \DateTimeZone('UTC')),
            20
        );
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2021-01-02 11:40:10', new \DateTimeZone('UTC')),
            10
        );
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2021-01-02 11:50:55', new \DateTimeZone('UTC')),
            50
        );

        $eventsApiRequestCountWithinLastHour = $this->eventsApiRequestCountWithinLastHour->execute(
            new \DateTimeImmutable('2021-01-02 12:10:00', new \DateTimeZone('UTC')),
            60
        );

        Assert::assertCount(2, $eventsApiRequestCountWithinLastHour);
        Assert::assertEquals(['event_count' => 50, 'updated' => '2021-01-02 11:50:55'], $eventsApiRequestCountWithinLastHour[0]);
        Assert::assertEquals(['event_count' => 10, 'updated' => '2021-01-02 11:40:10'], $eventsApiRequestCountWithinLastHour[1]);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
