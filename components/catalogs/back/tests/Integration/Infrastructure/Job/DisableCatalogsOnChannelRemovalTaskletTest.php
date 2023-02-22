<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class DisableCatalogsOnChannelRemovalTaskletTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogsOnChannelRemoval(): void
    {
        $this->getAuthenticatedInternalApiClient();

        $this->createUser('shopifi');
        $this->createUser('magenta');

        $this->createChannel('print');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['channels' => ['print', 'ecommerce']],
        );
        $this->createCatalog(
            id: 'b79b09a3-cb4c-45f8-a086-4f70cc17f521',
            name: 'Store FR',
            ownerUsername: 'magenta',
            catalogProductValueFilters: ['channels' => ['ecommerce']],
        );
        $this->createCatalog(
            id: '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            name: 'Store UK',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['channels' => ['print']],
        );
        $this->createCatalog(
            id: '97bdda65-8131-4c3b-a52d-25c5fe75462a',
            name: 'Store Ecommerce',
            ownerUsername: 'magenta',
            catalogProductMapping: ['custom_field' => ['scope' => 'ecommerce', 'source' => 'meta_title']],
        );
        $this->createCatalog(
            id: 'c58de220-1fac-4914-9c3c-eebb8444b6c1',
            name: 'Store Print',
            ownerUsername: 'magenta',
            catalogProductMapping: ['custom_field' => ['scope' => 'print', 'source' => 'meta_title']],
        );

        $this->removeChannel('print');
        $this->waitForQueuedJobs();

        $this->assertCatalogIsDisabled('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->assertCatalogIsDisabled('27c53e59-ee6a-4215-a8f1-2fccbb67ba0d');
        $this->assertCatalogIsDisabled('c58de220-1fac-4914-9c3c-eebb8444b6c1');
        $this->assertCatalogIsEnabled('b79b09a3-cb4c-45f8-a086-4f70cc17f521');
        $this->assertCatalogIsEnabled('97bdda65-8131-4c3b-a52d-25c5fe75462a');
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
