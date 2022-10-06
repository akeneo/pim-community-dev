<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsProductBelongingToCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;

class IsProductBelongingToCatalogQueryTest extends IntegrationTestCase
{
    private ?IsProductBelongingToCatalogQueryInterface $isProductBelongingToCatalogQuery;
    private ?GetCatalogQueryInterface $getCatalogQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->isProductBelongingToCatalogQuery = self::getContainer()->get(IsProductBelongingToCatalogQueryInterface::class);
        $this->getCatalogQuery = self::getContainer()->get(GetCatalogQueryInterface::class);
    }

    public function testAProductBelongingToTheCatalog(): void
    {
        $this->createUser('shopifi');
        $this->logAs('shopifi');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'shopifi');
        $this->enableCatalog($catalogId);
        $this->setCatalogProductSelection($catalogId, [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);
        $tshirt = $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute($catalogId);

        $isProductBelongingToCatalog = $this->isProductBelongingToCatalogQuery->execute($catalog, (string) $tshirt->getUuid());
        $this->assertTrue($isProductBelongingToCatalog);
    }

    public function testAProductNotBelongingToTheCatalog(): void
    {
        $this->createUser('shopifi');
        $this->logAs('shopifi');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'shopifi');
        $this->enableCatalog($catalogId);
        $this->setCatalogProductSelection($catalogId, [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);
        $tshirt = $this->createProduct('tshirt-blue', [new SetEnabled(false)]);

        $catalog = $this->getCatalogQuery->execute($catalogId);

        $isProductBelongingToCatalog = $this->isProductBelongingToCatalogQuery->execute($catalog, (string) $tshirt->getUuid());
        $this->assertFalse($isProductBelongingToCatalog);
    }
}
