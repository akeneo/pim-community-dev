<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\EventApiRequestsCountLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectEventApiRequestsCountPerDateTime;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class DbalSelectEventApiRequestsCountPerDateTimeIntegration extends TestCase
{
    private EventApiRequestsCountLoader $eventApiRequestsCountLoader;
    private SelectEventApiRequestsCountPerDateTime $selectEventApiRequestsCountPerDateTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventApiRequestsCountLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.event_api_request_count_loader'
        );
        $this->selectEventApiRequestsCountPerDateTime = $this->get(
            'akeneo_connectivity.connection.persistence.query.select_event_api_requests_count_per_datetime'
        );
    }

    public function test_it_selects_event_api_requests_count_per_datetime()
    {
        $this->eventApiRequestsCountLoader->createEventApiRequestsCount(
            new \DateTimeImmutable('2020-01-02 11:10:59', new \DateTimeZone('UTC')),
            6
        );
        $this->eventApiRequestsCountLoader->createEventApiRequestsCount(
            new \DateTimeImmutable('2020-01-02 11:11:11', new \DateTimeZone('UTC')),
            3
        );
        $this->eventApiRequestsCountLoader->createEventApiRequestsCount(
            new \DateTimeImmutable('2020-01-02 11:12:00', new \DateTimeZone('UTC')),
            2
        );

        Assert::assertEquals(
            3,
            $this->selectEventApiRequestsCountPerDateTime->execute(
                new \DateTimeImmutable('2020-01-02 11:11:58', new \DateTimeZone('UTC'))
            )
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
