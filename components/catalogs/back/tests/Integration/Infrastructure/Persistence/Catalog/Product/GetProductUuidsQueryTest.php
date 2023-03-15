<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductUuidsQueryTest extends IntegrationTestCase
{
    private ?GetCatalogQueryInterface $getCatalogQuery;
    private ?GetProductUuidsQueryInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->getCatalogQuery = self::getContainer()->get(GetCatalogQueryInterface::class);
        $this->query = self::getContainer()->get(GetProductUuidsQueryInterface::class);
    }

    public function testItGetsMatchingProductsUuids(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);
        $this->createProduct(Uuid::fromString('c07ad6f1-78a1-4add-84af-3c1d7d8484a3'), [new SetEnabled(false)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog);

        $this->assertEquals([
            '00380587-3893-46e6-a8c2-8fee6404cc9e',
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingSearchAfterAndLimit(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);
        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [new SetEnabled(true)]);
        $this->createProduct(Uuid::fromString('c07ad6f1-78a1-4add-84af-3c1d7d8484a3'), [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, '00380587-3893-46e6-a8c2-8fee6404cc9e', 1);

        $this->assertEquals([
            '8985de43-08bc-484d-aee0-4489a56ba02d',
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsWhenUsingScopableAndLocalizableCriterion(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('print', ['en_US', 'fr_FR']);
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
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => 'Bleu clair',
                    'scope' => 'print',
                    'locale' => 'fr_FR',
                ],
            ],
        );

        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [
            new SetTextValue('name', 'mobile', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'en_US', 'Light blue'),
            new SetTextValue('name', 'print', 'fr_FR', 'Bleu clair'),
        ]);
        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [
            new SetTextValue('name', 'mobile', 'fr_FR', 'Bleu clair'), // wrong channel
            new SetTextValue('name', 'print', 'en_US', 'Bleu clair'), // wrong locale
        ]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog);

        $this->assertEquals([
            '00380587-3893-46e6-a8c2-8fee6404cc9e',
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingUpdatedAfter(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00');

        $this->assertEquals([
            '8985de43-08bc-484d-aee0-4489a56ba02d',
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingUpdatedBefore(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, null, '2022-09-01T17:35:00+02:00');

        $this->assertEquals([
            '00380587-3893-46e6-a8c2-8fee6404cc9e',
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingUpdatedBeforeAndUpdatedAfter(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00', '2022-09-01T17:45:00+02:00');

        $this->assertEquals([
            '8985de43-08bc-484d-aee0-4489a56ba02d',
        ], $result);
    }

    public function testItSortsProductUuids(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [new SetEnabled(true)]);
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);
        $this->createProduct(Uuid::fromString('c07ad6f1-78a1-4add-84af-3c1d7d8484a3'), [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog);

        $this->assertEquals([
            '00380587-3893-46e6-a8c2-8fee6404cc9e',
            '8985de43-08bc-484d-aee0-4489a56ba02d',
            'c07ad6f1-78a1-4add-84af-3c1d7d8484a3',
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingRequiredPropertyInTheSchema(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('mobile', ['en_US', 'fr_FR']);
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);
        $this->createAttribute([
            'code' => 'size',
            'type' => 'pim_catalog_simpleselect',
            'scopable' => true,
            'localizable' => true,
            'options' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);
        $this->createAttribute([
            'code' => 'sale_countries',
            'type' => 'pim_catalog_multiselect',
            'scopable' => false,
            'localizable' => false,
            'options' => ['France', 'Canada', 'Italy', 'Brazil'],
        ]);

        $this->createCategory([
            'parent' => 'master',
            'code' => 'smartphone',
            'labels' => [
                'fr_FR' => 'Telephone intelligent',
                'en_US' => 'Smartphone',
            ],
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
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
                'size' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'sale_countries' => [
                    'source' => 'sale_countries',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'category' => [
                    'source' => 'categories',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => ['label_locale' => 'en_US'],
                ],
            ],
        );

        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [
            new SetTextValue('name', 'mobile', 'en_US', 'Blue'),
            new SetSimpleSelectValue('size', 'ecommerce', 'en_US', 'L'),
            new SetMultiSelectValue('sale_countries', null, null, ['France', 'Italy']),
            new SetCategories(['smartphone']),
        ]);

        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [
            new SetTextValue('name', 'mobile', 'en_US', ''),
            new SetSimpleSelectValue('size', 'ecommerce', 'en_US', 'L'),
            new SetCategories(['smartphone']),
        ]);

        $this->createProduct(Uuid::fromString('d5c1d01e-b18f-4a67-ac49-95ea2b1dcfd3'), [
            new SetTextValue('name', 'mobile', 'en_US', 'Blue'),
            new SetSimpleSelectValue('size', 'ecommerce', 'en_US', 'L'),
            new SetMultiSelectValue('sale_countries', null, null, ['France', 'Italy']),
        ]);

        $this->createProduct(Uuid::fromString('5595c455-d114-4ff8-bd0e-37fad972dec5'), [
            new SetSimpleSelectValue('size', 'ecommerce', 'en_US', 'L'),
            new SetMultiSelectValue('sale_countries', null, null, ['France', 'Italy']),
        ]);

        $this->createProduct(Uuid::fromString('a94553ff-90fd-413a-9819-0c3be9802f9b'), [
            new SetMultiSelectValue('sale_countries', null, null, ['France', 'Italy']),
        ]);

        $this->createProduct(Uuid::fromString('01bef4d4-d9ef-4648-8899-1b348caffecf'), [
            new SetCategories(['smartphone']),
        ]);

        $this->createProduct(Uuid::fromString('c07ad6f1-78a1-4add-84af-3c1d7d8484a3'));

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog);

        $this->assertEquals([
            '00380587-3893-46e6-a8c2-8fee6404cc9e',
        ], $result);
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
            },
            "size": {
              "type": "string"
            },
            "sale_countries": {
              "type": "string"
            },
            "category": {
              "type": "string"
            }
          },
          "required": ["title","size","sale_countries","category"]
        }
        JSON_WRAP;
    }
}
