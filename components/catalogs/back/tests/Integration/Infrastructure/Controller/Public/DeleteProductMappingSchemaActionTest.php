<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductMappingSchemaNotFoundException as ServiceApiProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductMappingSchemaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\DeleteProductMappingSchemaAction
 * @covers \Akeneo\Catalogs\Application\Handler\DeleteProductMappingSchemaHandler
 */
class DeleteProductMappingSchemaActionTest extends IntegrationTestCase
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
            productMappingSchema: $this->getValidSchemaData(),
            catalogProductMapping: [
                'uuid' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
                'name' => [
                    'source' => 'title',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        );

        $client->request(
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();

        Assert::assertEquals(204, $response->getStatusCode());

        $this->expectException(ServiceApiProductMappingSchemaNotFoundException::class);
        self::getContainer()->get(QueryBus::class)->execute(
            new GetProductMappingSchemaQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'),
        );

        /** @var Catalog $catalog */
        $catalog = self::getContainer()->get(GetCatalogQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        Assert::assertJsonStringEqualsJsonString(
            '[]',
            \json_encode($catalog->getProductMapping(), JSON_THROW_ON_ERROR),
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
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
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
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
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
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.1/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
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
