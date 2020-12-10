<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\EventsApiRequestCountLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectEventsApiRequestCountPerDateTime;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSelectEventsApiRequestCountPerDateTimeIntegration extends TestCase
{
    private EventsApiRequestCountLoader $eventsApiRequestCountLoader;
    private SelectEventsApiRequestCountPerDateTime $selectEventsApiRequestCountPerDateTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventsApiRequestCountLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.event_api_request_count_loader'
        );
        $this->selectEventsApiRequestCountPerDateTime = $this->get(
            'akeneo_connectivity.connection.persistence.query.select_events_api_request_count_per_datetime'
        );
    }

    public function test_it_selects_events_api_request_count_per_datetime()
    {
        $this->eventsApiRequestCountLoader->createEventApiRequestsCount(
            new \DateTimeImmutable('2020-01-02 11:10:59', new \DateTimeZone('UTC')),
            6
        );
        $this->eventsApiRequestCountLoader->createEventApiRequestsCount(
            new \DateTimeImmutable('2020-01-02 11:11:11', new \DateTimeZone('UTC')),
            3
        );
        $this->eventsApiRequestCountLoader->createEventApiRequestsCount(
            new \DateTimeImmutable('2020-01-02 11:12:00', new \DateTimeZone('UTC')),
            2
        );

        Assert::assertEquals(
            3,
            $this->selectEventsApiRequestCountPerDateTime->execute(
                new \DateTimeImmutable('2020-01-02 11:11:58', new \DateTimeZone('UTC'))
            )
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
