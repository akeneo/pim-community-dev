<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class DisableCatalogsOnLocaleDeactivationTaskletTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogsOnLocaleDeactivation(): void
    {
        $this->getAuthenticatedInternalApiClient();

        $this->createUser('shopifi');
        $this->createUser('magenta');
        $this->createCatalog(
            id: '975fef57-aa2c-42ad-a0ba-003af694ce11',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['locales' => ['fr_FR', 'en_US']]
        );
        $this->createCatalog(
            id: '29655723-2b78-4ae0-9ac2-2d00799db8df',
            name: 'Store FR',
            ownerUsername: 'magenta',
            catalogProductValueFilters: ['locales' => ['en_US']]
        );

        $this->disableLocale('fr_FR');
        $this->waitForQueuedJobs();

        $this->assertCatalogIsDisabled('975fef57-aa2c-42ad-a0ba-003af694ce11');
        $this->assertCatalogIsEnabled('29655723-2b78-4ae0-9ac2-2d00799db8df');
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
