<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectEventsApiRequestCountWithinLastHourQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence\DbalSelectEventsApiRequestCountWithinLastHourQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\EventsApiRequestCountLoader;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSelectEventsApiRequestCountWithinLastHourQueryIntegration extends TestCase
{
    private EventsApiRequestCountLoader $eventsApiRequestCountLoader;
    private SelectEventsApiRequestCountWithinLastHourQueryInterface $eventsApiRequestCountWithinLastHour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventsApiRequestCountLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.events_api_request_count_loader'
        );
        $this->eventsApiRequestCountWithinLastHour = $this->get(DbalSelectEventsApiRequestCountWithinLastHourQuery::class);
    }

    public function test_it_returns_an_empty_array_if_there_is_no_events_api_request_count_during_last_hour(): void
    {
        $eventsApiRequestCountWithinLastHour = $this->eventsApiRequestCountWithinLastHour->execute(
            new \DateTimeImmutable('2021-01-02 12:10:00', new \DateTimeZone('UTC')),
            60
        );

        Assert::assertIsArray($eventsApiRequestCountWithinLastHour);
        Assert::assertCount(0, $eventsApiRequestCountWithinLastHour);
    }

    public function test_it_selects_only_events_api_request_count_from_last_hour(): void
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

    protected function getConfiguration(): \Akeneo\Test\Integration\Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
