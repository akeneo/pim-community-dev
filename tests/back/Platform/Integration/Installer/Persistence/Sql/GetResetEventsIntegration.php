<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Installer\Persistence\Sql;

use Akeneo\Platform\Installer\Infrastructure\Persistence\Sql\GetResetEvents;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetResetEventsIntegration extends TestCase
{
    public function test_it_returns_empty_array_if_not_reset(): void
    {
        $resetEvents = ($this->getQuery())();

        $this->assertEmpty($resetEvents);
    }

    public function test_it_returns_reset_events(): void
    {
        $this->resetInstanceTwice();

        $resetEvents = ($this->getQuery())();

        $this->assertEqualsCanonicalizing([
            ['time' => new \DateTimeImmutable('2023-06-27T12:17:11+00:00')],
            ['time' => new \DateTimeImmutable('2023-06-28T12:17:11+00:00')],
        ], $resetEvents);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): GetResetEvents
    {
        return $this->get(GetResetEvents::class);
    }

    private function resetInstanceTwice(): void {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $connection->executeQuery(
<<<SQL
    INSERT INTO `pim_configuration` (`code`, `values`)
    VALUES
        ('reset_events', '[{\"time\": \"2023-06-27T12:17:11+00:00\"}, {\"time\": \"2023-06-28T12:17:11+00:00\"}]');
SQL
        );
    }
}
