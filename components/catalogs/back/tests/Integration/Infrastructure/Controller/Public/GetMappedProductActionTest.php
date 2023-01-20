<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\GetMappedProductAction
 * @covers \Akeneo\Catalogs\Application\Handler\GetMappedProductHandler
 */
class GetMappedProductActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();

        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
    }

    public function testItGetsMappedProductByCatalogIdAndProductUuid(): void
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
            'type' => 'pim_catalog_textarea',
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
            new SetSimpleSelectValue('size', 'print', 'en_US', 'm'),
        ]);

        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'read_products',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            productMappingSchema: $this->getProductMappingSchemaRaw(),
            catalogProductMapping: [
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
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ],
                ],
            ],
        );

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/8985de43-08bc-484d-aee0-4489a56ba02d',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expectedMappedProduct = [
            'uuid' => '8985de43-08bc-484d-aee0-4489a56ba02d',
            'title' => 'Blue name',
            'short_description' => 'Blue description',
            'size_label' => 'M',
        ];

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertSame($expectedMappedProduct, $payload);
    }

    public function testItReturnsAnErrorMessagePayloadWhenTheCatalogIsDisabled(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs', 'read_products',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name :'Store US',
            ownerUsername: 'shopifi',
            isEnabled: false,
        );

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/8985de43-08bc-484d-aee0-4489a56ba02d',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );
        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMessage = 'No product to synchronize. The catalog db1079b6-f397-4a6a-bae4-8658e64ad47c has been ' .
            'disabled on the PIM side. Note that you can get catalogs status with the GET /api/rest/v1/catalogs endpoint.';

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['error']);
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([]);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/8985de43-08bc-484d-aee0-4489a56ba02d',
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
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/8985de43-08bc-484d-aee0-4489a56ba02d',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMessage = 'Either catalog "db1079b6-f397-4a6a-bae4-8658e64ad47c" does not exist or you can\'t access'.
            ' it, or product "8985de43-08bc-484d-aee0-4489a56ba02d" does not exist or you do not have permission to access it.';

        Assert::assertEquals(404, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['message']);
    }

    public function testItReturnsNotFoundWhenProductDoesNotExist(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'read_products',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/8985de43-08bc-484d-aee0-4489a56ba02d',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMessage = 'Either catalog "db1079b6-f397-4a6a-bae4-8658e64ad47c" does not exist or you can\'t access'.
            ' it, or product "8985de43-08bc-484d-aee0-4489a56ba02d" does not exist or you do not have permission to access it.';

        Assert::assertEquals(404, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['message']);
    }

    public function testItReturnsNotFoundWhenTheProductIsFilteredOut(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'read_products',
        ]);

        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [
            new SetEnabled(false),
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
        );

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/8985de43-08bc-484d-aee0-4489a56ba02d',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMessage = 'Either catalog "db1079b6-f397-4a6a-bae4-8658e64ad47c" does not exist or you can\'t access'.
            ' it, or product "8985de43-08bc-484d-aee0-4489a56ba02d" does not exist or you do not have permission to access it.';

        Assert::assertEquals(404, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['message']);
    }

    public function testItReturnsAnErrorMessagePayloadWhenTheProductMappingSchemaIsMissing(): void
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
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $this->createChannel('print', ['en_US', 'fr_FR']);

        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [
            new SetTextValue('name', 'print', 'en_US', 'Blue name'),
            new SetTextValue('name', 'print', 'fr_FR', 'Nom Bleu'),
            new SetTextValue('description', 'print', null, 'Blue description'),
            new SetTextValue('size', null, null, 'Blue size'),
        ]);

        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'read_products',
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductMapping: [
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
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        );

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/8985de43-08bc-484d-aee0-4489a56ba02d',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals('Impossible to map the product: no product mapping schema available for this catalog.', $payload['error']);
    }

    public function testItReturnsNothingWhenAnAttributeIsMissing(): void
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
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $this->createChannel('print', ['en_US', 'fr_FR']);

        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [
            new SetTextValue('name', 'print', 'en_US', 'Blue name'),
            new SetTextValue('name', 'print', 'fr_FR', 'Nom Bleu'),
            new SetTextValue('description', 'print', null, 'Blue description'),
            new SetTextValue('size', null, null, 'Blue size'),
        ]);

        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Enabled invalid catalog',
            ownerUsername: 'shopifi',
            productMappingSchema: $this->getProductMappingSchemaRaw(),
            catalogProductMapping: [
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
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        );

        $this->removeAttribute('description');

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/8985de43-08bc-484d-aee0-4489a56ba02d',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertSame([
            'uuid' => '8985de43-08bc-484d-aee0-4489a56ba02d',
            'title' => 'Blue name',
            'size_label' => 'Blue size',
        ], $payload);
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
