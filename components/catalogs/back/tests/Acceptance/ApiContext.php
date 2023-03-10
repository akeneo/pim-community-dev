<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductMappingSchemaNotFoundException as ServiceApiProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductMappingSchemaQuery;
use Akeneo\Connectivity\Connection\ServiceApi\Model\ConnectedAppWithValidToken;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Behat\Behat\Context\Context;
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
    /** @var array<string, string> $files  */
    private array $files = [];

    public function __construct(
        KernelInterface $kernel,
        private AuthenticationContext $authentication,
        private QueryBus $queryBus,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
        $this->container->get('feature_flags')->enable('catalogs');
    }

    protected function getFileInfoKey(string $path): string
    {
        if (!\is_file($path)) {
            throw new \Exception(\sprintf('The path "%s" does not exist.', $path));
        }

        $fileStorer = $this->container->get('akeneo_file_storage.file_storage.file.file_storer');
        $fileInfo = $fileStorer->store(new \SplFileInfo($path), FileStorage::CATALOG_STORAGE_ALIAS);

        return $fileInfo->getKey();
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
            ),
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
                ],
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
                JSON_THROW_ON_ERROR,
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
            JSON_THROW_ON_ERROR,
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
        $productMappingSchemaNotFoundExceptionThrown = false;
        try {
            $this->queryBus->execute(new GetProductMappingSchemaQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'));
        } catch (ServiceApiProductMappingSchemaNotFoundException) {
            $productMappingSchemaNotFoundExceptionThrown = true;
        }

        Assert::assertTrue($productMappingSchemaNotFoundExceptionThrown);
    }

    /**
     * @Given an existing catalog with a product mapping
     */
    public function anExistingCatalogWithAProductMapping(): void
    {
        $connectedAppUserIdentifier = $this->getConnectedApp()->getUsername();
        $this->authentication->logAs($connectedAppUserIdentifier);

        // create enabled catalog with product selection criteria
        $catalog = new Catalog(
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
        );
        $this->upsertCatalogQuery->execute($catalog);

        // add product mapping schema
        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new UpdateProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode(
                <<<'JSON_WRAP'
                {
                  "$id": "https://example.com/product",
                  "$schema": "https://api.akeneo.com/mapping/product/0.0.10/schema",
                  "$comment": "My first schema !",
                  "title": "Product Mapping",
                  "description": "JSON Schema describing the structure of products expected by our application",
                  "type": "object",
                  "properties": {
                    "uuid": {
                      "type": "string"
                    },
                    "identifier": {
                      "type": "string"
                    },
                    "title": {
                      "type": "string"
                    },
                    "short_description": {
                      "type": "string"
                    },
                    "size": {
                      "type": "string"
                    },
                    "customization_drawings_count": {
                      "type": "number"
                    },
                    "customization_artists_count": {
                      "type": "string"
                    },
                    "price_number": {
                      "type": "number"
                    },
                    "release_date": {
                      "type": "string",
                      "format": "date-time"
                    },
                    "is_released": {
                      "type": "boolean"
                    },
                    "thumbnail": {
                      "type": "string",
                      "format": "uri"
                    },
                    "countries": {
                      "type": "string"
                    },
                    "type": {
                      "type": "string"
                    },
                    "weight": {
                      "type": "number"
                    }
                  },
                  "required": ["title"]
                }
                JSON_WRAP,
                false,
                512,
                JSON_THROW_ON_ERROR,
            ),
        ));

        // update product mapping (after product mapping schema as been added)
        $this->upsertCatalogQuery->execute(
            new Catalog(
                id: $catalog->getId(),
                name: $catalog->getName(),
                ownerUsername: $catalog->getOwnerUsername(),
                enabled: $catalog->isEnabled(),
                productSelectionCriteria: $catalog->getProductSelectionCriteria(),
                productValueFilters: $catalog->getProductValueFilters(),
                productMapping: [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'identifier' => [
                        'source' => 'sku',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                    ],
                    'short_description' => [
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
                        ],
                    ],
                    'customization_drawings_count' => [
                        'source' => 'drawings_customization_count',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'customization_artists_count' => [
                        'source' => 'artists_customization_count',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'price_number' => [
                        'source' => 'price',
                        'scope' => null,
                        'locale' => null,
                        'parameters' => [
                            'currency' => 'USD',
                        ],
                    ],
                    'release_date' => [
                        'source' => 'released_at',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'is_released' => [
                        'source' => 'is_released',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'countries' => [
                        'source' => 'sale_countries',
                        'scope' => null,
                        'locale' => null,
                        'parameters' => [
                            'label_locale' => 'en_US',
                        ],
                    ],
                    'thumbnail' => [
                        'source' => 'picture',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'type' => [
                        'source' => 'family',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'weight' => [
                        'source' => 'weight',
                        'scope' => null,
                        'locale' => null,
                        'parameters' => [
                            'unit' => 'GRAM',
                        ],
                    ],
                ],
            ),
        );

        // create products
        $adminUser = $this->authentication->getAdminUser();
        $this->authentication->logAs($adminUser->getUserIdentifier());

        $this->files = [
            'akeneoLogoImage' => $this->getFileInfoKey(__DIR__ . '/fixtures/akeneo.jpg'),
            'ziggyImage' => $this->getFileInfoKey(__DIR__ . '/fixtures/ziggy.png'),
        ];

        $products = [
            [
                'uuid' => '21a28f70-9cc8-4470-904f-aeda52764f73',
                'sku' => 't-shirt blue',
                'name' => 'T-shirt blue',
                'description' => 'Description blue',
                'size' => 'l',
                'drawings_customization_count' => '12',
                'artists_customization_count' => '7',
                'price' => ['USD' => 21],
                'released_at' => new \DateTimeImmutable('2023-01-12T00:00:00+00:00'),
                'is_released' => true,
                'picture' => $this->files['akeneoLogoImage'],
                'enabled' => true,
                'sale_countries' => [
                    'canada',
                    'brazil',
                ],
                'weight' => [
                    'unit' => 'MILLIGRAM',
                    'amount' => 12000,
                ],
            ],
            [
                'uuid' => '62071b85-67af-44dd-8db1-9bc1dab393e7',
                'sku' => 't-shirt green',
                'name' => 'T-shirt green',
                'description' => 'Description green',
                'size' => 'm',
                'drawings_customization_count' => '8',
                'artists_customization_count' => '5',
                'price' => ['USD' => 34],
                'released_at' => new \DateTimeImmutable('2023-01-10T00:00:00+00:00'),
                'is_released' => true,
                'picture' => $this->files['akeneoLogoImage'],
                'enabled' => false,
                'sale_countries' => [
                    'canada',
                    'italy',
                ],
                'weight' => [
                    'unit' => 'MILLIGRAM',
                    'amount' => 125.50,
                ],
            ],
            [
                'uuid' => 'a43209b0-cd39-4faf-ad1b-988859906030',
                'sku' => 't-shirt red',
                'name' => 'T-shirt red',
                'description' => 'Description red',
                'size' => 'xl',
                'drawings_customization_count' => '4',
                'artists_customization_count' => '2',
                'price' => ['USD' => 78.3],
                'released_at' => new \DateTimeImmutable('2042-01-01T00:00:00+00:00'),
                'is_released' => false,
                'picture' => $this->files['ziggyImage'],
                'enabled' => true,
                'sale_countries' => [
                    'france',
                    'brazil',
                ],
                'weight' => [
                    'unit' => 'MILLIGRAM',
                    'amount' => 125,
                ],
            ],
            [
                'uuid' => '7343e656-a114-4956-bb5e-2f5f1317b6d2',
                'sku' => 't-shirt yellow',
                'name' => '',
                'description' => 'Description yellow',
                'size' => 'xl',
                'drawings_customization_count' => '4',
                'artists_customization_count' => '2',
                'price' => ['USD' => 78.3],
                'released_at' => new \DateTimeImmutable('2042-01-01T00:00:00+00:00'),
                'is_released' => false,
                'picture' => $this->files['ziggyImage'],
                'enabled' => true,
                'sale_countries' => [
                    'france',
                    'brazil',
                ],
                'weight' => [
                    'unit' => 'MILLIGRAM',
                    'amount' => 125,
                ],
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
        $this->createAttribute([
            'code' => 'drawings_customization_count',
            'type' => 'pim_catalog_number',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'artists_customization_count',
            'type' => 'pim_catalog_number',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'price',
            'type' => 'pim_catalog_price_collection',
            'decimals_allowed' => true,
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'released_at',
            'type' => 'pim_catalog_date',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'is_released',
            'type' => 'pim_catalog_boolean',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'picture',
            'type' => 'pim_catalog_image',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'sale_countries',
            'type' => 'pim_catalog_multiselect',
            'scopable' => false,
            'localizable' => false,
            'options' => ['France', 'Canada', 'Italy', 'Brazil'],
        ]);
        $this->createAttribute([
            'code' => 'weight',
            'type' => 'pim_catalog_metric',
            'scopable' => false,
            'localizable' => false,
            'metric_family' => 'Weight',
            'default_metric_unit' => 'KILOGRAM',
            'decimals_allowed' => true,
        ]);

        $this->createFamily('t-shirt', [
            'sku',
            'name',
            'description',
            'size',
            'drawings_customization_count',
            'artists_customization_count',
            'price',
            'released_at',
            'is_released',
            'sale_countries',
            'weight',
        ]);

        $bus = $this->container->get('pim_enrich.product.message_bus');

        foreach ($products as $product) {
            $command = UpsertProductCommand::createWithUuid(
                $adminUser->getId(),
                ProductUuid::fromUuid(Uuid::fromString($product['uuid'])),
                [
                    new SetIdentifierValue('sku', $product['sku']),
                    new SetFamily('t-shirt'),
                    new SetEnabled((bool) $product['enabled']),
                    new SetTextValue('name', 'ecommerce', 'en_US', $product['name']),
                    new SetTextareaValue('description', 'ecommerce', 'en_US', $product['description']),
                    new SetSimpleSelectValue('size', 'ecommerce', 'en_US', $product['size']),
                    new SetNumberValue('drawings_customization_count', null, null, $product['drawings_customization_count']),
                    new SetNumberValue('artists_customization_count', null, null, $product['artists_customization_count']),
                    new SetDateValue('released_at', null, null, $product['released_at']),
                    new SetBooleanValue('is_released', null, null, $product['is_released']),
                    new SetMultiSelectValue('sale_countries', null, null, $product['sale_countries']),
                    new SetImageValue('picture', null, null, $product['picture']),
                    new SetPriceCollectionValue('price', null, null, \array_map(
                        static fn (float $amount, string $currency): PriceValue => new PriceValue($amount, $currency),
                        $product['price'],
                        \array_keys($product['price']),
                    )),
                    new SetMeasurementValue('weight', null, null, $product['weight']['amount'], $product['weight']['unit']),
                ],
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
                'identifier' => 't-shirt blue',
                'title' => 'T-shirt blue',
                'short_description' => 'Description blue',
                'size' => 'L',
                'customization_drawings_count' => 12,
                'customization_artists_count' => '7',
                'price_number' => 21,
                'release_date' => '2023-01-12T00:00:00+00:00',
                'is_released' => true,
                'thumbnail' => 'http://localhost/api/rest/v1/media-files/' . $this->files['akeneoLogoImage'] . '/download',
                'countries' => 'Brazil, Canada',
                'type' => 't-shirt',
                'weight' => 12,
            ],
            [
                'uuid' => 'a43209b0-cd39-4faf-ad1b-988859906030',
                'identifier' => 't-shirt red',
                'title' => 'T-shirt red',
                'short_description' => 'Description red',
                'size' => 'XL',
                'customization_drawings_count' => 4,
                'customization_artists_count' => '2',
                'price_number' => 78.3,
                'release_date' => '2042-01-01T00:00:00+00:00',
                'is_released' => false,
                'thumbnail' => 'http://localhost/api/rest/v1/media-files/' . $this->files['ziggyImage'] . '/download',
                'countries' => 'Brazil, France',
                'type' => 't-shirt',
                'weight' => 0.125,
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
            'identifier' => 't-shirt blue',
            'title' => 'T-shirt blue',
            'short_description' => 'Description blue',
            'size' => 'L',
            'customization_drawings_count' => 12,
            'customization_artists_count' => '7',
            'price_number' => 21,
            'release_date' => '2023-01-12T00:00:00+00:00',
            'is_released' => true,
            'thumbnail' => 'http://localhost/api/rest/v1/media-files/' . $this->files['akeneoLogoImage'] . '/download',
            'countries' => 'Brazil, Canada',
            'type' => 't-shirt',
            'weight' => 12,
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
            $this->container->get(GetLocalesQueryInterface::class)->execute(),
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

    /**
     * @param array<string> $attributes
     */
    private function createFamily(string $code, array $attributes): void
    {
        $family = $this->container->get('pim_catalog.factory.family')->create();
        $this->container->get('pim_catalog.updater.family')->update($family, [
            'code' => $code,
            'attributes' => $attributes,
        ]);
        $this->container->get('pim_catalog.saver.family')->save($family);
    }
}
