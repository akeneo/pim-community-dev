<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductMappingSchemaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\UpdateProductMappingSchemaAction
 * @covers \Akeneo\Catalogs\Application\Handler\UpdateProductMappingSchemaHandler
 */
class UpdateProductMappingSchemaActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItUpdatesTheCatalogProductMappingSchema(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        $client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getValidProductMappingSchema(),
        );

        $response = $client->getResponse();

        Assert::assertEquals(204, $response->getStatusCode());

        $productMappingSchema = self::getContainer()->get(QueryBus::class)->execute(
            new GetProductMappingSchemaQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'),
        );
        Assert::assertJsonStringEqualsJsonString(
            $this->getValidProductMappingSchema(),
            \json_encode($productMappingSchema, JSON_THROW_ON_ERROR),
        );
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([]);
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        $client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getValidProductMappingSchema(),
        );

        $response = $client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotExist(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getValidProductMappingSchema(),
        );

        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotBelongToCurrentUser(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->createUser('magendo');
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'magendo',
        );

        $client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getValidProductMappingSchema(),
        );

        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsBadRequestWhenPayloadIsNotValidJson(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        $client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{',
        );

        $response = $client->getResponse();

        Assert::assertEquals(400, $response->getStatusCode());
    }

    public function testItReturnsUnprocessableEntityWhenPayloadIsNotAValidSchema(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        $client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{}',
        );

        $response = $client->getResponse();

        Assert::assertEquals(422, $response->getStatusCode());
    }

    public function testItCreatesTheProductMapping(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        $client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getValidProductMappingSchema(),
        );

        /** @var Catalog $catalog */
        $catalog = self::getContainer()->get(GetCatalogQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $expectedProductMapping = [
            'uuid' => [
                'source' => 'uuid',
                'locale' => null,
                'scope' => null,
            ],
            'name' => [
                'source' => null,
                'locale' => null,
                'scope' => null,
            ],
            'body_html' => [
                'source' => null,
                'locale' => null,
                'scope' => null,
            ],
        ];

        Assert::assertEquals($expectedProductMapping, $catalog->getProductMapping());
    }

    public function testItUpdatesTheProductMappingWithAddedAndRemovedTargets(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            productMappingSchema: $this->getValidProductMappingSchema(),
            catalogProductMapping: [
                'uuid' => [
                    'source' => 'uuid',
                    'locale' => null,
                    'scope' => null,
                ],
                'name' => [
                    'source' => 'Title',
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                ],
                'body_html' => [
                    'source' => null,
                    'locale' => null,
                    'scope' => null,
                ],
            ],
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

        $client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $newProductMappingSchema,
        );

        /** @var Catalog $catalog */
        $catalog = self::getContainer()->get(GetCatalogQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $expectedProductMapping = [
            'uuid' => [
                'source' => 'uuid',
                'locale' => null,
                'scope' => null,
            ],
            'name' => [
                'source' => 'Title',
                'locale' => 'en_US',
                'scope' => 'ecommerce',
            ],
            'name_from_erp' => [
                'source' => null,
                'locale' => null,
                'scope' => null,
            ],
        ];

        Assert::assertEquals($expectedProductMapping, $catalog->getProductMapping());
    }

    private function getValidProductMappingSchema(): string
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
            "name": {
              "type": "string"
            },
            "body_html": {
              "title": "Description",
              "description": "Product description in raw HTML",
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }
}
