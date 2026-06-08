<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\Service;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\GetDelayUntilNextRequest;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\EventsApiRequestCountLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetDelayUntilNextRequestIntegration extends TestCase
{
    private GetDelayUntilNextRequest $getDelayUntilNextRequest;
    private EventsApiRequestCountLoader $eventsApiRequestCountLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getDelayUntilNextRequest = $this->get(GetDelayUntilNextRequest::class);
        $this->eventsApiRequestCountLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.events_api_request_count_loader'
        );
    }

    public function test_it_returns_a_delay_when_the_limit_is_reached(): void
    {
        $eventDateTime = new \DateTimeImmutable('2021-01-02 11:50:00', new \DateTimeZone('UTC'));
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount($eventDateTime, 100);

        $currentDateTime = new \DateTimeImmutable('2021-01-02 12:10:00', new \DateTimeZone('UTC'));
        $delay = $this->getDelayUntilNextRequest->execute($currentDateTime, 50);

        Assert::assertIsInt($delay);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
