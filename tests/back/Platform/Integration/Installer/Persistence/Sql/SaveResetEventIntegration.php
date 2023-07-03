<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Installer\Persistence\Sql;

use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\GetResetData;
use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\SaveResetEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SaveResetEventIntegration extends TestCase
{
    public function test_it_saves_reset_event_for_the_first_time(): void
    {
        $resetData = $this->getResetData();
        
        $this->assertNull($resetData);
        
        $dateTime = new \DateTimeImmutable();
        $this->getQuery()->withDatetime($dateTime);

        $resetData = $this->getResetData();

        $this->assertSame([
            'reset_events' => [
                ['time' => $dateTime->format('c')],
            ],
        ], $resetData);
    }

    public function test_it_preserves_previous_events(): void
    {
        $dateTime = new \DateTimeImmutable();
        $this->getQuery()->withDatetime($dateTime);

        $dateTimeLater = new \DateTimeImmutable();
        $this->getQuery()->withDatetime($dateTimeLater);

        $resetData = $this->getResetData();

        $this->assertSame([
            'reset_events' => [
                ['time' => $dateTime->format('c')],
                ['time' => $dateTimeLater->format('c')],
            ],
        ], $resetData);
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

    private function getResetData(): ?array
    {
        return $this->get(GetResetData::class)->__invoke();
    }
}
