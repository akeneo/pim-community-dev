<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\ProductMappingSchema;

use Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\GetProductMappingSchemaQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\UpdateProductMappingSchemaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\UpdateProductMappingSchemaQuery
 */
class UpdateProductMappingSchemaQueryTest extends IntegrationTestCase
{
    private ?UpdateProductMappingSchemaQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(UpdateProductMappingSchemaQuery::class);
    }

    public function testItUpdatesProductMappingSchema(): void
    {
        $this->createUser('test');
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'test',
            productMappingSchema: $this->getValidSchemaData(),
        );

        $newProductMappingSchema = <<<'JSON_WRAP'
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
            "name": {
              "type": "string"
            },
            "name_from_erp": {
              "title": "ERP Name",
              "description": "Name from the ERP",
              "type": "string"
            }
          }
        }
        JSON_WRAP;

        $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c', $newProductMappingSchema);

        $queryGetProductMappingSchema = self::getContainer()->get(GetProductMappingSchemaQuery::class);
        $updatedSchema = $queryGetProductMappingSchema->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $expectedSchema = \json_decode($newProductMappingSchema, true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals($expectedSchema, $updatedSchema);
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.4/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
            "uuid": {
              "type": "string"
            },
            "name": {
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }
}
