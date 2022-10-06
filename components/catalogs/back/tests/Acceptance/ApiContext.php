<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Connectivity\Connection\ServiceApi\Model\ConnectedAppWithValidToken;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
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

        // create  enabled catalog with product selection criteria
        $this->upsertCatalogQuery->execute(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                $connectedAppUserIdentifier,
                true,
                [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
                []
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
     * @Then the response should contain an empty list
     */
    public function theResponseShouldContainAnEmptyList(): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEmpty($payload['_embedded']['items']);
    }
}
