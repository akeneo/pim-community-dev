<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsProductBelongingToCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Ramsey\Uuid\Uuid;

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
        $this->createCatalog(
            id: $catalogId,
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );
        $tshirtBlue = $this->createProduct('tshirt-blue', [new SetEnabled(true)]);
        $this->createProduct('tshirt-red', [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute($catalogId);

        $isProductBelongingToCatalog = $this->isProductBelongingToCatalogQuery->execute($catalog, (string) $tshirtBlue->getUuid());
        $this->assertTrue($isProductBelongingToCatalog);
    }

    public function testAProductDoesNotBelongToTheCatalogWhenProductSelectionHasEnabledCriteria(): void
    {
        $this->createUser('shopifi');
        $this->logAs('shopifi');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog(
            id: $catalogId,
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );
        $tshirt = $this->createProduct('tshirt-blue', [new SetEnabled(false)]);
        $this->createProduct('tshirt-red', [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute($catalogId);

        $isProductBelongingToCatalog = $this->isProductBelongingToCatalogQuery->execute($catalog, (string) $tshirt->getUuid());
        $this->assertFalse($isProductBelongingToCatalog);
    }

    public function testAProductDoesNotBelongToTheCatalogWhenMappingHasRequiredAttribute(): void
    {
        $this->createUser('shopifi');
        $this->logAs('shopifi');

        $this->createChannel('mobile', ['en_US', 'fr_FR']);
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            productMappingSchema: $this->getValidSchemaData(),
            catalogProductMapping: [
                'uuid' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
                'title' => [
                    'source' => 'name',
                    'scope' => 'mobile',
                    'locale' => 'en_US',
                ],
            ],
        );

        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [
            new SetEnabled(true),
            new SetTextValue('name', 'mobile', 'en_US', 'Blue'),
        ]);

        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [
            new SetEnabled(true),
            new SetTextValue('name', 'mobile', 'en_US', ''),
        ]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $isProductBelongingToCatalog = $this->isProductBelongingToCatalogQuery->execute($catalog, '8985de43-08bc-484d-aee0-4489a56ba02d');

        $this->assertFalse($isProductBelongingToCatalog);
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.10/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
            "uuid": {
              "type": "string"
            },
            "title": {
              "type": "string"
            }
          },
          "required": ["title"]
        }
        JSON_WRAP;
    }
}
