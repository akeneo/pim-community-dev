<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Installer\Persistence\Sql;

use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\GetResetEvents;
use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\SaveResetEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SaveResetEventIntegration extends TestCase
{
    public function test_it_saves_reset_event_for_the_first_time(): void
    {
        $resetEvents = $this->getResetEvents();
        
        $this->assertEmpty($resetEvents);
        
        $dateTime = new \DateTimeImmutable();
        $this->getQuery()->withDatetime($dateTime);

        $resetEvents = $this->getResetEvents();

        $this->assertEqualsCanonicalizing([
            ['time' => new \DateTimeImmutable($dateTime->format('c'))],
        ], $resetEvents);
    }

    public function test_it_preserves_previous_events(): void
    {
        $dateTime = new \DateTimeImmutable();
        $this->getQuery()->withDatetime($dateTime);

        $dateTimeLater = new \DateTimeImmutable();
        $this->getQuery()->withDatetime($dateTimeLater);

        $resetEvents = $this->getResetEvents();

        $this->assertEqualsCanonicalizing([
            ['time' => new \DateTimeImmutable($dateTime->format('c'))],
            ['time' => new \DateTimeImmutable($dateTimeLater->format('c'))],
        ], $resetEvents);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): SaveResetEvent
    {
        return $this->get(SaveResetEvent::class);
    }

    private function getResetEvents(): ?array
    {
        return ($this->get(GetResetEvents::class))();
    }
}
