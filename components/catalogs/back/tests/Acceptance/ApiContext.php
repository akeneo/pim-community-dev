<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException as ServiceApiProductSchemaMappingNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductMappingSchemaQuery;
use Akeneo\Connectivity\Connection\ServiceApi\Model\ConnectedAppWithValidToken;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Behat\Behat\Context\Context;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiContext implements Context
{
    private ContainerInterface $container;
    private ?Response $response = null;
    private ?ConnectedAppWithValidToken $connectedApp = null;
    private ?KernelBrowser $client = null;

    public function __construct(
        KernelInterface $kernel,
        private AuthenticationContext $authentication,
        private QueryBus $queryBus,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    private function getConnectedApp(): ConnectedAppWithValidToken
    {
        return $this->connectedApp ??= $this->authentication->createConnectedApp([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
            'read_products',
        ]);
    }

    private function getConnectedAppClient(): KernelBrowser
    {
        return $this->client ??= $this->authentication->createAuthenticatedClient($this->getConnectedApp());
    }

    /**
     * @Given an existing catalog
     */
    public function anExistingCatalog(): void
    {
        $connectedAppUserIdentifier = $this->getConnectedApp()->getUsername();
        $this->authentication->logAs($connectedAppUserIdentifier);

        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $connectedAppUserIdentifier,
        ));
    }

    /**
     * @Given a disabled catalog
     */
    public function aDisabledCatalog(): void
    {
        $connectedAppUserIdentifier = $this->getConnectedApp()->getUsername();
        $this->authentication->logAs($connectedAppUserIdentifier);

        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $connectedAppUserIdentifier,
        ));
    }

    /**
     * @Given several existing catalogs
     */
    public function severalExistingCatalogs(): void
    {
        $connectedAppUserIdentifier = $this->getConnectedApp()->getUsername();
        $this->authentication->logAs($connectedAppUserIdentifier);

        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $connectedAppUserIdentifier,
        ));
        $commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            $connectedAppUserIdentifier,
        ));
        $commandBus->execute(new CreateCatalogCommand(
            '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            'Store UK',
            $connectedAppUserIdentifier,
        ));
    }

    /**
     * @Given an enabled catalog with product selection criteria
     */
    public function anEnabledCatalogWithProductSelectionCriteria(): void
    {
        $connectedAppUserIdentifier = $this->getConnectedApp()->getUsername();
        $this->authentication->logAs($connectedAppUserIdentifier);

        // create enabled catalog with product selection criteria
        $this->upsertCatalogQuery->execute(
            new Catalog(
                id:'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                name:'Store US',
                ownerUsername: $connectedAppUserIdentifier,
                enabled:  true,
                productSelectionCriteria: [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
                productValueFilters: [],
                productMapping: [],
            )
        );

        // create products
        $adminUser = $this->authentication->getAdminUser();
        $this->authentication->logAs($adminUser->getUserIdentifier());

        $bus = $this->container->get('pim_enrich.product.message_bus');

        $products = [
            [
                'uuid' => '21a28f70-9cc8-4470-904f-aeda52764f73',
                'identifier' => 't-shirt blue',
                'enabled' => true,
            ],
            [
                'uuid' => '62071b85-67af-44dd-8db1-9bc1dab393e7',
                'identifier' => 't-shirt green',
                'enabled' => false,
            ],
            [
                'uuid' => 'a43209b0-cd39-4faf-ad1b-988859906030',
                'identifier' => 't-shirt red',
                'enabled' => true,
            ],
        ];

        foreach ($products as $product) {
            $command = UpsertProductCommand::createWithUuid(
                $adminUser->getId(),
                ProductUuid::fromUuid(Uuid::fromString($product['uuid'])),
                [
                    new SetIdentifierValue('sku', $product['identifier']),
                    new SetEnabled((bool) $product['enabled']),
                ]
            );

            $bus->dispatch($command);
        }

        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @When the external application retrieves the catalog using the API
     */
    public function theExternalApplicationRetrievesTheCatalogUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @When the external application retrieves the catalogs using the API
     */
    public function theExternalApplicationRetrievesTheCatalogsUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs',
            parameters: [
                'limit' => 2,
                'page' => 1,
            ],
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the catalog details
     */
    public function theResponseShouldContainTheCatalogDetails(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertArrayHasKey('id', $payload);
        Assert::assertArrayHasKey('name', $payload);
        Assert::assertArrayHasKey('enabled', $payload);
    }

    /**
     * @Then the response should contain catalogs details
     */
    public function theResponseShouldContainCatalogsDetails(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertCount(2, $payload['_embedded']['items']);

        Assert::assertArrayHasKey('id', $payload['_embedded']['items'][0]);
        Assert::assertArrayHasKey('name', $payload['_embedded']['items'][0]);
        Assert::assertArrayHasKey('enabled', $payload['_embedded']['items'][0]);
    }

    /**
     * @When the external application creates a catalog using the API
     */
    public function theExternalApplicationCreatesACatalogUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'POST',
            uri: '/api/rest/v1/catalogs',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: \json_encode([
                'name' => 'Store US',
            ]),
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(201, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the catalog id
     */
    public function theResponseShouldContainTheCatalogId(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertArrayHasKey('id', $payload);
    }

    /**
     * @Then the catalog should exist in the PIM
     */
    public function theCatalogShouldExistInThePim(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $catalog = $this->queryBus->execute(new GetCatalogQuery($payload['id']));

        Assert::assertNotNull($catalog);
    }

    /**
     * @When the external application deletes a catalog using the API
     */
    public function theExternalApplicationDeletesACatalogUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'DELETE',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(204, $this->response->getStatusCode());
    }

    /**
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty(): void
    {
        Assert::assertEmpty($this->response->getContent());
    }

    /**
     * @Then the catalog should be removed from the PIM
     */
    public function theCatalogShouldBeRemovedFromThePim(): void
    {
        $catalog = $this->queryBus->execute(new GetCatalogQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'));

        Assert::assertNull($catalog);
    }

    /**
     * @When the external application updates a catalog using the API
     */
    public function theExternalApplicationUpdatesACatalogUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'PATCH',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: \json_encode([
                'name' => 'Store US [NEW]',
            ]),
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the catalog should be updated in the PIM
     */
    public function theCatalogShouldBeUpdatedInThePim(): void
    {
        $catalog = $this->queryBus->execute(new GetCatalogQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'));

        Assert::assertNotNull($catalog);
        Assert::assertEquals('Store US [NEW]', $catalog->getName());
    }

    /**
     * @When the external application retrieves the product's identifiers using the API
     */
    public function theExternalApplicationRetrievesTheProductsIdentifiersUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/product-identifiers',
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain only the product's identifiers from the selection
     */
    public function theResponseShouldContainOnlyTheProductsIdentifiersFromTheSelection(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertCount(2, $payload['_embedded']['items']);

        // Comes from the step anEnabledCatalogSetsUpWithAProductSelectionCriteria
        $expectedIdentifiers = [
            't-shirt blue',
            't-shirt red',
        ];

        Assert::assertSame($expectedIdentifiers, $payload['_embedded']['items']);
    }

    /**
     * @When the external application retrieves the product's uuids using the API
     */
    public function theExternalApplicationRetrievesTheProductsUuidsUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/product-uuids',
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain only the product's uuids from the selection
     */
    public function theResponseShouldContainOnlyTheProductsUuidsFromTheSelection(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertCount(2, $payload['_embedded']['items']);

        // Comes from the step anEnabledCatalogSetsUpWithAProductSelectionCriteria
        $expectedUuids = [
            '21a28f70-9cc8-4470-904f-aeda52764f73',
            'a43209b0-cd39-4faf-ad1b-988859906030',
        ];

        Assert::assertSame($expectedUuids, $payload['_embedded']['items']);
    }

    /**
     * @When the external application retrieves the products using the API
     */
    public function theExternalApplicationRetrievesTheProductsUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products',
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain only the products from the selection
     */
    public function theResponseShouldContainOnlyTheProductsFromTheSelection(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertCount(2, $payload['_embedded']['items']);

        // Comes from the step anEnabledCatalogSetsUpWithAProductSelectionCriteria
        $expectedUuids = [
            '21a28f70-9cc8-4470-904f-aeda52764f73',
            'a43209b0-cd39-4faf-ad1b-988859906030',
        ];

        foreach ($payload['_embedded']['items'] as $item) {
            Assert::assertContains($item['uuid'], $expectedUuids);
            Assert::assertTrue($item['enabled']);
        }
    }

    /**
     * @Then the response should contain an error message
     */
    public function theResponseShouldContainAnErrorMessage(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertFalse(\array_key_exists('_embedded', $payload));
        Assert::assertTrue(\array_key_exists('error', $payload));
    }

    /**
     * @Given an existing catalog with a product mapping schema
     */
    public function anExistingCatalogWithAProductMappingSchema(): void
    {
        $connectedAppUserIdentifier = $this->getConnectedApp()->getUsername();
        $this->authentication->logAs($connectedAppUserIdentifier);

        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $connectedAppUserIdentifier,
        ));
        $commandBus->execute(new UpdateProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode(
                <<<'JSON_WRAP'
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
                JSON_WRAP,
                false,
                512,
                JSON_THROW_ON_ERROR
            ),
        ));
    }

    /**
     * @When the external application retrieves the catalog product mapping schema using the API
     */
    public function theExternalApplicationRetrievesTheCatalogProductMappingSchemaUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @When the external application updates a catalog product mapping schema using the API
     */
    public function theExternalApplicationUpdatesACatalogProductMappingSchemaUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'PUT',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: <<<'JSON_WRAP'
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
            JSON_WRAP,
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(204, $this->response->getStatusCode());
    }

    /**
     * @Then the catalog product mapping schema should be updated in the PIM
     */
    public function theCatalogProductMappingSchemaShouldBeUpdatedInThePim(): void
    {
        $productMappingSchema = \json_encode(
            $this->queryBus->execute(new GetProductMappingSchemaQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c')),
            JSON_THROW_ON_ERROR
        );

        Assert::assertJsonStringEqualsJsonString(
            <<<'JSON_WRAP'
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
            JSON_WRAP,
            $productMappingSchema,
        );
    }

    /**
     * @Then the response should contain the catalog product mapping schema
     */
    public function theResponseShouldContainTheCatalogProductMappingSchema(): void
    {
        Assert::assertJsonStringEqualsJsonString(
            <<<'JSON_WRAP'
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
            JSON_WRAP,
            $this->response->getContent(),
        );
    }

    /**
     * @When the external application deletes a catalog product mapping schema using the API
     */
    public function theExternalApplicationDeletesACatalogProductMappingSchemaUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'DELETE',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(204, $this->response->getStatusCode());
    }

    /**
     * @Then the catalog product mapping schema should be empty in the PIM
     */
    public function theCatalogProductMappingSchemaShouldBeEmptyInThePim(): void
    {
        $productSchemaMappingNotFoundExceptionThrown = false;
        try {
            $this->queryBus->execute(new GetProductMappingSchemaQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'));
        } catch (ServiceApiProductSchemaMappingNotFoundException) {
            $productSchemaMappingNotFoundExceptionThrown = true;
        }

        Assert::assertTrue($productSchemaMappingNotFoundExceptionThrown);
    }

    /**
     * @Given an existing catalog with a product mapping
     */
    public function anExistingCatalogWithAProductMapping(): void
    {
        $connectedAppUserIdentifier = $this->getConnectedApp()->getUsername();
        $this->authentication->logAs($connectedAppUserIdentifier);

        // create enabled catalog with product selection criteria and product mapping
        $this->upsertCatalogQuery->execute(
            new Catalog(
                id:'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                name:'Store US',
                ownerUsername: $connectedAppUserIdentifier,
                enabled:  true,
                productSelectionCriteria: [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
                productValueFilters: [],
                productMapping: [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                    ],
                    'description' => [
                        'source' => 'description',
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                    ],
                    'size' => [
                        'source' => 'size',
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                        'parameters' => [
                            'label_locale' => 'en_US',
                        ]
                    ],
                ],
            )
        );

        // add product mapping schema
        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new UpdateProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode(
                <<<'JSON_WRAP'
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
                    "description": {
                      "type": "string"
                    },
                    "size": {
                      "type": "string"
                    }
                  }
                }
                JSON_WRAP,
                false,
                512,
                JSON_THROW_ON_ERROR
            ),
        ));

        // create products
        $adminUser = $this->authentication->getAdminUser();
        $this->authentication->logAs($adminUser->getUserIdentifier());

        $bus = $this->container->get('pim_enrich.product.message_bus');

        $products = [
            [
                'uuid' => '21a28f70-9cc8-4470-904f-aeda52764f73',
                'identifier' => 't-shirt blue',
                'name' => 'T-shirt blue',
                'description' => 'Description blue',
                'size' => 'l',
                'enabled' => true,
            ],
            [
                'uuid' => '62071b85-67af-44dd-8db1-9bc1dab393e7',
                'identifier' => 't-shirt green',
                'name' => 'T-shirt green',
                'description' => 'Description green',
                'size' => 'm',
                'enabled' => false,
            ],
            [
                'uuid' => 'a43209b0-cd39-4faf-ad1b-988859906030',
                'identifier' => 't-shirt red',
                'name' => 'T-shirt red',
                'description' => 'Description red',
                'size' => 'xl',
                'enabled' => true,
            ],
        ];

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
            'localizable' => true,
        ]);
        $this->createAttribute([
            'code' => 'size',
            'type' => 'pim_catalog_simpleselect',
            'scopable' => true,
            'localizable' => true,
            'options' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);

        foreach ($products as $product) {
            $command = UpsertProductCommand::createWithUuid(
                $adminUser->getId(),
                ProductUuid::fromUuid(Uuid::fromString($product['uuid'])),
                [
                    new SetIdentifierValue('sku', $product['identifier']),
                    new SetEnabled((bool) $product['enabled']),
                    new SetTextValue('name', 'ecommerce', 'en_US', $product['name']),
                    new SetTextareaValue('description', 'ecommerce', 'en_US', $product['description']),
                    new SetSimpleSelectValue('size', 'ecommerce', 'en_US', $product['size']),
                ]
            );

            $bus->dispatch($command);
        }

        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @When the external application gets mapped products using the API
     */
    public function theExternalApplicationGetsMappedProductsUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products',
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the mapped products
     */
    public function theResponseShouldContainTheMappedProducts(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertCount(2, $payload['_embedded']['items']);

        $expectedMappedProducts = [
            [
                'uuid' => '21a28f70-9cc8-4470-904f-aeda52764f73',
                'title' => 'T-shirt blue',
                'description' => 'Description blue',
                'size' => 'L',
            ],
            [
                'uuid' => 'a43209b0-cd39-4faf-ad1b-988859906030',
                'title' => 'T-shirt red',
                'description' => 'Description red',
                'size' => 'XL',
            ],
        ];

        Assert::assertSame($expectedMappedProducts, $payload['_embedded']['items']);
    }

    /**
     * @When the external application gets mapped product using the API
     */
    public function theExternalApplicationGetsMappedProductUsingTheApi(): void
    {
        $this->authentication->logAs($this->getConnectedApp()->getUsername());

        $this->getConnectedAppClient()->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products/21a28f70-9cc8-4470-904f-aeda52764f73',
        );

        $this->response = $this->getConnectedAppClient()->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the mapped product
     */
    public function theResponseShouldContainTheMappedProduct(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMappedProducts = [
            'uuid' => '21a28f70-9cc8-4470-904f-aeda52764f73',
            'title' => 'T-shirt blue',
            'description' => 'Description blue',
            'size' => 'L',
        ];

        Assert::assertSame($expectedMappedProducts, $payload);
    }

    /**
     * @param array{
     *     code: string,
     *     type: string,
     *     available_locales?: array<string>,
     *     group?: string,
     *     scopable: bool,
     *     localizable: bool,
     *     options?: array<string>
     * } $data
     */
    private function createAttribute(array $data): void
    {
        $data = \array_merge([
            'group' => 'other',
        ], $data);

        $options = $data['options'] ?? [];
        unset($data['options']);

        $attribute = $this->container->get('pim_catalog.factory.attribute')->create();
        $this->container->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $this->container->get('pim_catalog.saver.attribute')->save($attribute);

        if ([] !== $options) {
            $this->createAttributeOptions($attribute, $options);
            $this->container->get('pim_connector.doctrine.cache_clearer')->clear();
        }
    }

    private function createAttributeOptions(AttributeInterface $attribute, array $codes): void
    {
        $factory = $this->container->get('pim_catalog.factory.attribute_option');
        $locales = \array_map(
            static fn ($locale) => $locale['code'],
            $this->container->get(GetLocalesQueryInterface::class)->execute()
        );

        $options = [];

        foreach ($codes as $i => $code) {
            /** @var AttributeOptionInterface $option */
            $option = $factory->create();
            $option->setCode(\strtolower(\trim(\preg_replace('/[^A-Za-z0-9-]+/', '_', $code))));
            $option->setAttribute($attribute);
            $option->setSortOrder($i);

            foreach ($locales as $locale) {
                $value = new AttributeOptionValue();
                $value->setOption($option);
                $value->setLocale($locale);
                $value->setLabel($code);

                $option->addOptionValue($value);
            }

            $options[] = $option;
        }

        $this->container->get('pim_catalog.saver.attribute_option')->saveAll($options);
    }
}
