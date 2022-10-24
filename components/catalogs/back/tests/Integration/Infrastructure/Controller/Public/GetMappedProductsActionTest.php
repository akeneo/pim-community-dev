<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use PHPUnit\Framework\Assert;
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

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsPaginatedMappedProductsByCatalogId(): void
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
        $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode($this->getProductMappingSchemaRaw(), false, 512, JSON_THROW_ON_ERROR),
        ));

        $this->setCatalogProductMapping('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            "title" => [
                "source" => "name",
                "scope" => "ecommerce",
                "locale" => "en_US",
            ],
            "family" => [
                "source" => "family",
                "scope" => null,
                "locale" => "en_US",
            ],
            "category" => [
                "source" => "category",
                "scope" => null,
                "locale" => "en_US",
            ],
            "group" => [
                "source" => "group",
                "scope" => null,
                "locale" => "en_US",
            ],
        ]);

        $this->createFamily(['code' => 'familyA', 'labels' => ['en_US' => 'Family A', 'fr_FR' => 'Famille A']]);

        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);

        $this->createCategory(['code' => 'categoryA', 'labels' => ['en_US' => 'Category A', 'fr_FR' => 'Categorie A']]);
        $this->createCategory(['code' => 'categoryB', 'labels' => ['en_US' => 'Category B', 'fr_FR' => 'Categorie B']]);


        $this->createGroupType(['code' => 'groupeType', 'labels' => ['en_US' => 'Group type', 'fr_FR' => 'Groupe type']]);
        $this->createGroup(['code' => 'groupA', 'type' => 'groupeType', 'labels' => ['en_US' => 'Group A', 'fr_FR' => 'Groupe A']]);
        $this->createGroup(['code' => 'groupB', 'type' => 'groupeType', 'labels' => ['en_US' => 'Group B', 'fr_FR' => 'Groupe B']]);

        $this->createProduct('tshirt-blue', [
            new SetTextValue('name', 'ecommerce', 'en_US', 'Blue'),
            new SetFamily('familyA'),
            new SetCategories(['categoryA', 'categoryB']),
            new SetGroups(['GroupA', 'GroupB'])
        ]);

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
        $expectedMappedProducts = [
            [
                'title' => 'Blue',
                'family' => 'Family A',
                'category' => 'Category A,Category B',
                'group' => 'Group A,Group B'
            ],
        ];

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals($expectedMappedProducts, $payload['_embedded']['items']);
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
            "title": {
              "type": "string"
            },
            "family": {
              "type": "string"
            },
            "category": {
              "type": "string"
            },
            "group": {
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }
}
