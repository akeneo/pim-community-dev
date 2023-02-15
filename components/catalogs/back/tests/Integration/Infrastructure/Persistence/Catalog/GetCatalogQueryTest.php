<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery
 */
class GetCatalogQueryTest extends IntegrationTestCase
{
    private ?GetCatalogQuery $query;
    private ?Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(GetCatalogQuery::class);
    }

    public function testItGetsCatalog(): void
    {
        $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog(
            id: $id,
            name: 'Store US',
            ownerUsername: 'test',
            isEnabled: false,
            catalogProductValueFilters: [
                'channels' => ['ecommerce', 'print'],
            ],
        );

        $result = $this->query->execute($id);

        $expected = new Catalog(
            $id,
            'Store US',
            'test',
            false,
            [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            ['channels' => ['ecommerce', 'print']],
            [],
        );

        $this->assertEquals($expected, $result);
    }

    public function testItGetsCatalogWithOrderedProductMapping(): void
    {
        $this->createUser('test');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'test',
            productMappingSchema: $this->getProductMappingSchemaRaw(),
            catalogProductMapping: [
                'short_description' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                ],
                'size_label' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                ],
                'title' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                ],
                'uuid' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        );

        $result = $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $expectedProductMappingKeys = ['uuid', 'title', 'short_description', 'size_label'];

        $this->assertEquals($expectedProductMappingKeys, \array_keys($result->getProductMapping()));
    }

    public function testItThrowsWhenCatalogDoesNotExist(): void
    {
        $this->expectException(CatalogNotFoundException::class);

        $this->query->execute('017c3d69-5c7d-4cd9-9d19-4ffe856026a3');
    }

    public function testItThrowsWhenProductSelectionCriteriaIsInvalid(): void
    {
        $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog($id, 'Store US', 'test');
        $this->setInvalidCatalogProductSelectionCriteria($id);

        $this->setCatalogProductValueFilters(
            $id,
            ['channels' => ['ecommerce', 'print']],
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid JSON in product_selection_criteria column');

        $this->query->execute($id);
    }

    public function testItThrowsWhenProductValueFiltersIsInvalid(): void
    {
        $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog($id, 'Store US', 'test');
        $this->setInvalidCatalogProductValueFilters($id);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid JSON in product_value_filters column');

        $this->query->execute($id);
    }

    private function setInvalidCatalogProductSelectionCriteria(string $id): void
    {
        $this->connection->executeQuery(
            'UPDATE akeneo_catalog SET product_selection_criteria = :criteria WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'criteria' => 'invalid_product_selection_criteria',
            ],
            [
                'criteria' => Types::JSON,
            ],
        );
    }

    private function setInvalidCatalogProductValueFilters(string $id): void
    {
        $this->connection->executeQuery(
            'UPDATE akeneo_catalog SET product_value_filters = :filters WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'filters' => 'invalid_product_value_filters',
            ],
            [
                'filters' => Types::JSON,
            ],
        );
    }

    private function getProductMappingSchemaRaw(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.2/schema",
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
            "short_description": {
              "type": "string"
            },
            "size_label": {
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }
}
