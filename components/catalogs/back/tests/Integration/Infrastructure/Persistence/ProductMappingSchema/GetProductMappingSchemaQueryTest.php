<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\ProductMappingSchema;

use Akeneo\Catalogs\Application\Exception\ProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\GetProductMappingSchemaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\GetProductMappingSchemaQuery
 */
class GetProductMappingSchemaQueryTest extends IntegrationTestCase
{
    private ?GetProductMappingSchemaQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetProductMappingSchemaQuery::class);
    }

    public function testItGetsProductMappingSchema(): void
    {
        $this->createUser('test');
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'test',
            productMappingSchema: $this->getValidSchemaData(),
        );

        $schema = $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $expectedSchema = \json_decode($this->getValidSchemaData(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals($expectedSchema, $schema);
    }

    public function testItThrowsIfTheProductMappingSchemaDoesNotExist(): void
    {
        $this->expectException(ProductMappingSchemaNotFoundException::class);

        $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');
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
