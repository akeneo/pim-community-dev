<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
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
    private ?KernelBrowser $client = null;
    private ?CommandBus $commandBus;
    private ?QueryBus $queryBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);
        $this->queryBus = self::getContainer()->get(QueryBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItUpdatesTheCatalogProductMappingSchema(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $this->client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getProductMappingSchemaV002(),
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(204, $response->getStatusCode());

        $productMappingSchema = $this->queryBus->execute(
            new GetProductMappingSchemaQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'),
        );
        Assert::assertJsonStringEqualsJsonString(
            $this->getProductMappingSchemaV002(),
            \json_encode($productMappingSchema, JSON_THROW_ON_ERROR),
        );
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $this->client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getProductMappingSchemaV002(),
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotExist(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getProductMappingSchemaV002(),
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotBelongToCurrentUser(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);
        $this->createUser('magendo');
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'magendo',
        ));

        $this->client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getProductMappingSchemaV002(),
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsBadRequestWhenPayloadIsNotValidJson(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $this->client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{',
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(400, $response->getStatusCode());
    }

    public function testItReturnsUnprocessableEntityWhenPayloadIsNotAValidSchema(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $this->client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{}',
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(422, $response->getStatusCode());
    }

    /**
     * @dataProvider validVersionedProductMappingSchemaProvider
     */
    public function testItCreatesTheProductMapping(string $productMappingSchema, $expectedProductMapping): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $this->client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $productMappingSchema,
        );

        /** @var Catalog $catalog */
        $catalog = self::getContainer()->get(GetCatalogQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        Assert::assertEquals($expectedProductMapping, $catalog->getProductMapping());
    }

    public function validVersionedProductMappingSchemaProvider(): array
    {
        return [
            'v0.0.1' => [
                'productMappingSchema' => $this->getProductMappingSchemaV001(),
                'expectedProductMapping' => [
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
                ],
            ],
            'v0.0.2' => [
                'productMappingSchema' => $this->getProductMappingSchemaV002(),
                'expectedProductMapping' => [
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
                ],
            ],
        ];
    }

    /**
     * @dataProvider validVersionedProductMappingSchemaProviderWithMoreAndLessTargets
     */
    public function testItUpdatesTheProductMapping(string $productMappingSchema, $expectedProductMapping): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'write_catalogs',
        ]);

        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $currentProductMapping = [
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
        ];

        $this->setCatalogProductMapping('db1079b6-f397-4a6a-bae4-8658e64ad47c', $currentProductMapping);

        $this->client->request(
            'PUT',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $productMappingSchema,
        );

        /** @var Catalog $catalog */
        $catalog = self::getContainer()->get(GetCatalogQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        Assert::assertEquals($expectedProductMapping, $catalog->getProductMapping());
    }

    public function validVersionedProductMappingSchemaProviderWithMoreAndLessTargets(): array
    {
        return [
            'v0.0.1' => [
                'productMappingSchema' => $this->getProductMappingSchemaV001WithMoreAndLessTargets(),
                'expectedProductMapping' => [
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
                ],
            ],
            'v0.0.2' => [
                'productMappingSchema' => $this->getProductMappingSchemaV002WithMoreAndLessTargets(),
                'expectedProductMapping' => [
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
                ],
            ],
        ];
    }

    private function getProductMappingSchemaV001(): string
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

    private function getProductMappingSchemaV002(): string
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

    private function getProductMappingSchemaV001WithMoreAndLessTargets(): string
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
            "name_from_erp": {
              "title": "ERP Name",
              "description": "Name from the ERP",
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }

    private function getProductMappingSchemaV002WithMoreAndLessTargets(): string
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
            "name_from_erp": {
              "title": "ERP Name",
              "description": "Name from the ERP",
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }
}
