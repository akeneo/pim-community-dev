<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class DisableCatalogsOnCurrencyDeactivationTaskletTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogsOnCurrencyDeactivation(): void
    {
        $this->getAuthenticatedInternalApiClient();

        $this->createUser('shopifi');
        $this->createUser('magenta');
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['currencies' => ['EUR', 'USD']],
        );
        $this->createCatalog(
            id: 'b79b09a3-cb4c-45f8-a086-4f70cc17f521',
            name: 'Store FR',
            ownerUsername: 'magenta',
            catalogProductValueFilters: ['currencies' => ['USD']],
        );
        $this->createCatalog(
            id: '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            name: 'Store UK',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['currencies' => ['EUR']],
        );

        $this->disableCurrency('EUR');
        $this->waitForQueuedJobs();

        $this->assertCatalogIsDisabled('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->assertCatalogIsDisabled('27c53e59-ee6a-4215-a8f1-2fccbb67ba0d');
        $this->assertCatalogIsEnabled('b79b09a3-cb4c-45f8-a086-4f70cc17f521');
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
