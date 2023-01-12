<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\GetMappedProductsAction
 * @covers \Akeneo\Catalogs\Application\Handler\GetMappedProductsHandler
 */
class GetMappedProductsActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client = null;
    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();

        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
    }

    public function testItGetsPaginatedMappedProductsByCatalogId(): void
    {
        $this->logAs('admin');

        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);
        $this->createAttribute([
            'code' => 'description',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'size',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['XS', 'S', 'M', 'L', 'XL'],
            'scopable' => true,
            'localizable' => true,
        ]);


        $this->createChannel('print', ['en_US', 'fr_FR']);

        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [
            new SetTextValue('name', 'print', 'en_US', 'Blue name'),
            new SetTextValue('name', 'print', 'fr_FR', 'Nom Bleu'),
            new SetTextValue('description', 'print', null, 'Blue description'),
            new SetSimpleSelectValue('size', 'ecommerce', 'en_US', 'm'),
        ]);
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [
            new SetTextValue('name', 'print', 'en_US', 'Green name'),
            new SetTextValue('description', 'print', null, 'Green description'),
            new SetSimpleSelectValue('size', 'print', 'en_US', 'l'),
        ]);
        $this->createProduct(Uuid::fromString('9fe842c4-6185-470b-b9a8-abc2306b0e4b'), [
            new SetTextValue('name', 'print', 'en_US', 'Red name'),
            new SetTextValue('description', 'print', null, 'Red description'),
            new SetSimpleSelectValue('size', 'print', 'en_US', 'xl'),
        ]);

        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'read_products',
        ]);

        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi'
        ));
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode($this->getProductMappingSchemaRaw(), false, 512, JSON_THROW_ON_ERROR),
        ));
        $this->setCatalogProductMapping('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'title' => [
                'source' => 'name',
                'scope' => 'print',
                'locale' => 'en_US',
            ],
            'short_description' => [
                'source' => 'description',
                'scope' => 'print',
                'locale' => null,
            ],
            'size_label' => [
                'source' => 'size',
                'scope' => 'print',
                'locale' => 'en_US',
            ],
        ]);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products',
            [
                'limit' => 2,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expectedMappedProducts = [
            [
                'uuid' => '00380587-3893-46e6-a8c2-8fee6404cc9e',
                'title' => 'Green name',
                'short_description' => 'Green description',
                'size_label' => 'L',
            ],
            [
                'uuid' => '8985de43-08bc-484d-aee0-4489a56ba02d',
                'title' => 'Blue name',
                'short_description' => 'Blue description',
            ],
        ];

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertCount(2, $payload['_embedded']['items']);
        Assert::assertSame($expectedMappedProducts, $payload['_embedded']['items']);
        Assert::assertEquals(
            'http://localhost/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products?search_after=8985de43-08bc-484d-aee0-4489a56ba02d&limit=2',
            $payload['_links']['next']['href']
        );

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products?search_after=8985de43-08bc-484d-aee0-4489a56ba02d&limit=2',
            [
                'limit' => 2,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $page2Response = $this->client->getResponse();
        $payloadPage2 = \json_decode($page2Response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expectedMappedProducts2 = [
            [
                'uuid' => '9fe842c4-6185-470b-b9a8-abc2306b0e4b',
                'title' => 'Red name',
                'short_description' => 'Red description',
                'size_label' => 'XL',
            ],
        ];

        Assert::assertEquals(200, $page2Response->getStatusCode());
        Assert::assertCount(1, $payloadPage2['_embedded']['items']);
        Assert::assertSame($expectedMappedProducts2, $payloadPage2['_embedded']['items']);
    }

    public function testItReturnsBadRequestWhenPaginationIsInvalid(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'shopifi');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products',
            [
                'limit' => -1,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(422, $response->getStatusCode());
    }

    public function testItReturnsAnErrorMessagePayloadWhenTheCatalogIsDisabled(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs', 'read_products',
        ]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi'
        ));

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );
        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMessage = 'No products to synchronize. The catalog db1079b6-f397-4a6a-bae4-8658e64ad47c has been ' .
            'disabled on the PIM side. Note that you can get catalogs status with the GET /api/rest/v1/catalogs endpoint.';

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['message']);
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([]);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotExist(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsAnErrorMessagePayloadWhenTheProductMappingSchemaIsMissing(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs', 'read_products',
        ]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi'
        ));
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals('Impossible to map products: no product mapping schema available for this catalog.', $payload['message']);
    }

    private function getProductMappingSchemaRaw(): string
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
