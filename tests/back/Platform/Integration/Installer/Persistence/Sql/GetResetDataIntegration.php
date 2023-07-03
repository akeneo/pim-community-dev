<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Installer\Persistence\Sql;

use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\GetResetData;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetResetDataIntegration extends TestCase
{
    public function test_it_returns_null_if_not_reset(): void
    {
        $resetData = $this->getQuery()->__invoke();

        $this->assertNull($resetData);
    }

    public function test_it_returns_reset_data(): void
    {
        $this->resetInstanceTwice();

        $resetData = $this->getQuery()->__invoke();

        $this->assertSame([
            'reset_events' => [
                ['time' => '2023-06-27T12:17:11+00:00'],
                ['time' => '2023-06-28T12:17:11+00:00'],
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

    private function getQuery(): GetResetData
    {
        return $this->get(GetResetData::class);
    }

    private function resetInstanceTwice(): void {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $connection->executeQuery(
<<<SQL
    INSERT INTO `pim_configuration` (`code`, `values`)
    VALUES
        ('reset_data', '{\"reset_events\": [{\"time\": \"2023-06-27T12:17:11+00:00\"}, {\"time\": \"2023-06-28T12:17:11+00:00\"}]}');
SQL
        );
    }
}
