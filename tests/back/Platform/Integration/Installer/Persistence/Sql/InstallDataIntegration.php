<?php

namespace AkeneoTest\Platform\Integration\Installer\Persistence\Sql;

use Akeneo\Platform\Installer\Infrastructure\Persistence\Sql\InstallData;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class InstallDataIntegration extends TestCase
{
    public function test_it_adds_install_datetime(): void
    {
        $query = $this->getQuery();

        $query->withDatetime(new \DateTimeImmutable('2022-12-13'));

        $installData = $this->getInstallData();

        $this->assertArrayHasKey('database_installed_at', $installData);
        $this->assertStringStartsWith('2022-12-13T00:00:00', $installData['database_installed_at']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): InstallData
    {
        return $this->get(InstallData::class);
    }

    private function getInstallData(): array
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $result = $connection->executeQuery(
            'SELECT `values` FROM pim_configuration WHERE code = "install_data";'
        );

        return \json_decode($result->fetchOne(), true);
    }
}
