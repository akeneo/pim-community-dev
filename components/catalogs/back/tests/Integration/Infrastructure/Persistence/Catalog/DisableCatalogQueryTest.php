<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\DisableCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class DisableCatalogQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogByUUID(): void
    {
        $this->createUser('shopifi');
        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $catalogIdFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';

        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi');
        $this->createCatalog($catalogIdFR, 'Store FR', 'shopifi');

        self::getContainer()->get(DisableCatalogQuery::class)->execute($catalogIdUS);

        $this->assertCatalogIsDisabled($catalogIdUS);
        $this->assertCatalogIsEnabled($catalogIdFR);
    }

    public function testItDoesNothingOnCatalogAlreadyDisabled(): void
    {
        $this->createUser('shopifi');
        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi', false);

        self::getContainer()->get(DisableCatalogQuery::class)->execute($catalogIdUS);

        $this->assertCatalogIsDisabled($catalogIdUS);
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
