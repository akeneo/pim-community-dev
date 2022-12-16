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
        $this->createCatalog(
            id: 'ab594b66-146b-4e35-9676-6b582a73f3de',
            name: 'Store BE',
            ownerUsername: 'magenta',
            catalogProductMapping: ['meta_title' => ['scope' => null, 'locale' => 'en_US', 'source' => 'meta_title']]
        );
        $this->createCatalog(
            id: 'af80cdbe-3e5b-4f47-acef-8878bee72dfb',
            name: 'Store BE',
            ownerUsername: 'magenta',
            catalogProductMapping: ['meta_title' => ['scope' => null, 'locale' => 'fr_FR', 'source' => 'meta_title']]
        );

        $this->disableLocale('fr_FR');
        $this->waitForQueuedJobs();

        $this->assertCatalogIsDisabled('975fef57-aa2c-42ad-a0ba-003af694ce11');
        $this->assertCatalogIsDisabled('af80cdbe-3e5b-4f47-acef-8878bee72dfb');
        $this->assertCatalogIsEnabled('29655723-2b78-4ae0-9ac2-2d00799db8df');
        $this->assertCatalogIsEnabled('ab594b66-146b-4e35-9676-6b582a73f3de');
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
