<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class DisableCatalogsOnCategoryRemovalTaskletTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogOnCategoryRemoval(): void
    {
        $this->getAuthenticatedInternalApiClient();
        $this->createCategory([
            'code' => 'tshirt',
            'labels' => ['en_US' => 'T-shirt'],
        ]);
        $this->createCategory([
            'code' => 'scanner',
            'labels' => ['en_US' => 'Scanner'],
        ]);

        $idCatalogUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idCatalogFR = 'b79b09a3-cb4c-45f8-a086-4f70cc17f521';
        $this->createUser('shopifi');
        $this->createUser('magenta');
        $this->createCatalog($idCatalogUS, 'Store US', 'shopifi');
        $this->createCatalog($idCatalogFR, 'Store FR', 'magenta');
        $this->enableCatalog($idCatalogUS);
        $this->enableCatalog($idCatalogFR);

        $this->setCatalogProductSelection($idCatalogUS, [
            [
                'field' => 'category',
                'operator' => Operator::IN_LIST,
                'value' => ['tshirt'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($idCatalogFR, [
            [
                'field' => 'category',
                'operator' => Operator::IN_LIST,
                'value' => ['scanner'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $this->removeCategory('tshirt');

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
