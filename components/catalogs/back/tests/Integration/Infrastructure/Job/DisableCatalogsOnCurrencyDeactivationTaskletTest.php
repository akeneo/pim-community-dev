<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;

class DisableCatalogsOnCurrencyDeactivationTaskletTest extends IntegrationTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = static::getContainer()->get('database_connection');

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogsOnCurrencyDeactivation(): void
    {
        $this->getAuthenticatedInternalApiClient();

        $idCatalogUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idCatalogFR = 'b79b09a3-cb4c-45f8-a086-4f70cc17f521';
        $this->createUser('shopifi');
        $this->createUser('magenta');

        $this->createCatalog($idCatalogUS, 'Store US', 'shopifi', true, null,
            ['currencies' => ['EUR', 'USD']]
        );
        $this->createCatalog($idCatalogFR, 'Store FR', 'magenta', true, null,
            ['currencies' => ['USD']]
        );

        $this->disableCurrency('EUR');
        $this->waitForQueuedJobs();

        $this->assertCatalogIsDisabled($idCatalogUS);
        $this->assertCatalogIsEnabled($idCatalogFR);


    }

    private function assertCatalogIsDisabled(string $id): void
    {
        $catalog = $this->getCatalog($id);
        $this->assertFalse($catalog->isEnabled());
    }

    private function assertCatalogIsEnabled(string $id): void
    {
        $catalog = $this->getCatalog($id);
        $this->assertTrue($catalog->isEnabled());
    }
}
