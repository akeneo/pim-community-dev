<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\ProductMappingSchema;

use Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\ExistsProductMappingSchemaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\ExistsProductMappingSchemaQuery
 */
class ExistsProductMappingSchemaQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testProductMappingSchemaExists(): void
    {
        $this->createUser('test');
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'test',
            productMappingSchema: $this->getValidSchemaData(),
        );

        Assert::assertTrue(self::getContainer()->get(ExistsProductMappingSchemaQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c'));
    }

    public function testProductMappingSchemaDoesNotExistsForExistingCatalog(): void
    {
        $this->createUser('test');
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'test',
        );

        Assert::assertFalse(self::getContainer()->get(ExistsProductMappingSchemaQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c'));
    }

    public function testProductMappingSchemaDoesNotExistsForUnexistingCatalog(): void
    {
        Assert::assertFalse(self::getContainer()->get(ExistsProductMappingSchemaQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c'));
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
