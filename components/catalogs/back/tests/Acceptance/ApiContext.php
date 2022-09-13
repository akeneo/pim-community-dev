<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductSelectionCriteriaQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
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
    private ?KernelBrowser $client = null;

    public function __construct(
        KernelInterface $kernel,
        private AuthenticationContext $authentication,
        private QueryBus $queryBus,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
        private UpdateCatalogProductSelectionCriteriaQueryInterface $updateCatalogProductSelectionCriteriaQuery,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    /**
     * @Given an existing catalog
     */
    public function anExistingCatalog(): void
    {
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
            'read_products',
        ]);

        /** @var UserInterface $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $user->getUserIdentifier(),
        ));
    }

    /**
     * @Given several existing catalogs
     */
    public function severalExistingCatalogs(): void
    {
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        /** @var UserInterface $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $user->getUserIdentifier(),
        ));
        $commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            $user->getUserIdentifier(),
        ));
        $commandBus->execute(new CreateCatalogCommand(
            '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            'Store UK',
            $user->getUserIdentifier(),
        ));
    }

    /**
     * @Given the catalog is enabled
     */
    public function theCatalogIsEnabled(): void
    {
        /** @var UserInterface $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $this->upsertCatalogQuery->execute(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $user->getUserIdentifier(),
            true,
        );
    }

    /**
     * @Given the catalog has product selection criteria
     */
    public function theCatalogHasProductSelectionCriteria(): void
    {
        $productSelectionCriteria = [
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ],
        ];

        $this->updateCatalogProductSelectionCriteriaQuery->execute(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            $productSelectionCriteria,
        );
    }

    /**
     * @Given the following products exist:
     */
    public function theFollowingProductsExist(TableNode $table): void
    {
        $bus = $this->container->get('pim_enrich.product.message_bus');

        /** @var UserInterface $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $hash = $table->getHash();

        foreach ($hash as $row) {
            $command = UpsertProductCommand::createWithUuid(
                $user->getId(),
                ProductUuid::fromUuid(Uuid::fromString($row['uuid'])),
                [
                    new SetIdentifierValue('sku', $row['identifier']),
                    new SetEnabled((bool) $row['enabled']),
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
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $this->client->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $this->response = $this->client->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @When the external application retrieves the catalogs using the API
     */
    public function theExternalApplicationRetrievesTheCatalogsUsingTheApi(): void
    {
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $this->client->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs',
            parameters: [
                'limit' => 2,
                'page' => 1,
            ],
        );

        $this->response = $this->client->getResponse();

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
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $this->client->request(
            method: 'POST',
            uri: '/api/rest/v1/catalogs',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: \json_encode([
                'name' => 'Store US',
            ]),
        );

        $this->response = $this->client->getResponse();

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
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $this->client->request(
            method: 'DELETE',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $this->response = $this->client->getResponse();

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
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $this->client->request(
            method: 'PATCH',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: \json_encode([
                'name' => 'Store US [NEW]',
            ]),
        );

        $this->response = $this->client->getResponse();

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
     * @When the external application retrieves the product identifiers using the API
     */
    public function theExternalApplicationRetrievesTheProductIdentifiersUsingTheApi(): void
    {
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
            'read_products',
        ]);

        $this->client->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/product-identifiers',
        );

        $this->response = $this->client->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the following product identifiers:
     */
    public function theResponseShouldContainTheFilteredProductIdentifiers(TableNode $table): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $hash = $table->getHash();

        Assert::assertCount(\count($hash), $payload['_embedded']['items']);

        foreach ($hash as $row) {
            Assert::assertContains($row['identifier'], $payload['_embedded']['items']);
        }
    }

    /**
     * @When the external application retrieves the product uuids using the API
     */
    public function theExternalApplicationRetrievesTheProductUuidsUsingTheApi(): void
    {
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
            'read_products',
        ]);

        $this->client->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/product-uuids',
        );

        $this->response = $this->client->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the following product uuids:
     */
    public function theResponseShouldContainTheFilteredProductUuids(TableNode $table): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $hash = $table->getHash();

        Assert::assertCount(\count($hash), $payload['_embedded']['items']);

        foreach ($hash as $row) {
            Assert::assertContains($row['uuid'], $payload['_embedded']['items']);
        }
    }

    /**
     * @When the external application retrieves the products using the API
     */
    public function theExternalApplicationRetrievesTheProductsUsingTheApi(): void
    {
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
            'read_products',
        ]);

        $this->client->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products',
        );

        $this->response = $this->client->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the following products:
     */
    public function theResponseShouldContainTheFilteredProducts(TableNode $table): void
    {
        $payload = \json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $hash = $table->getHash();

        Assert::assertCount(\count($hash), $payload['_embedded']['items']);

        foreach ($hash as $row) {
            $matchingProduct = null;
            foreach ($payload['_embedded']['items'] as $product) {
                if ($product['uuid'] === $row['uuid']) {
                    $matchingProduct = $product;
                    break;
                }
            }

            Assert::assertNotNull($matchingProduct, 'the product is missing');

            Assert::assertEquals($row['identifier'], $matchingProduct['values']['sku'][0]['data']);
        }
    }
}
