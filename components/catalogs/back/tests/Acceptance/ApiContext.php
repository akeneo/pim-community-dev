<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
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
    private ?Response $response;
    private ?KernelBrowser $client;

    public function __construct(
        KernelInterface $kernel,
        private AuthenticationContext $authentication,
        private QueryBus $queryBus,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    /**
     * @Given an existing catalog
     */
    public function anExistingCatalog()
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
    }

    /**
     * @Given several existing catalogs
     */
    public function severalExistingCatalogs()
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
     * @When the external application retrieves the catalog using the API
     */
    public function theExternalApplicationRetrievesTheCatalogUsingTheApi()
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
    public function theExternalApplicationRetrievesTheCatalogsUsingTheApi()
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
    public function theResponseShouldContainTheCatalogDetails()
    {
        $payload = \json_decode($this->response->getContent(), true);

        Assert::assertArrayHasKey('id', $payload);
        Assert::assertArrayHasKey('name', $payload);
        Assert::assertArrayHasKey('enabled', $payload);
    }

    /**
     * @Then the response should contain catalogs details
     */
    public function theResponseShouldContainCatalogsDetails()
    {
        $payload = \json_decode($this->response->getContent(), true);

        Assert::assertCount(2, $payload['_embedded']['items']);

        Assert::assertArrayHasKey('id', $payload['_embedded']['items'][0]);
        Assert::assertArrayHasKey('name', $payload['_embedded']['items'][0]);
        Assert::assertArrayHasKey('enabled', $payload['_embedded']['items'][0]);
    }

    /**
     * @When the external application creates a catalog using the API
     */
    public function theExternalApplicationCreatesACatalogUsingTheApi()
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
    public function theResponseShouldContainTheCatalogId()
    {
        $payload = \json_decode($this->response->getContent(), true);

        Assert::assertArrayHasKey('id', $payload);
    }

    /**
     * @Then the catalog should exist in the PIM
     */
    public function theCatalogShouldExistInThePim()
    {
        $payload = \json_decode($this->response->getContent(), true);

        $catalog = $this->queryBus->execute(new GetCatalogQuery($payload['id']));

        Assert::assertNotNull($catalog);
    }

    /**
     * @When the external application deletes a catalog using the API
     */
    public function theExternalApplicationDeletesACatalogUsingTheApi()
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
    public function theResponseShouldBeEmpty()
    {
        Assert::assertEmpty($this->response->getContent());
    }

    /**
     * @Then the catalog should be removed from the PIM
     */
    public function theCatalogShouldBeRemovedFromThePim()
    {
        $catalog = $this->queryBus->execute(new GetCatalogQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'));

        Assert::assertNull($catalog);
    }

    /**
     * @When the external application updates a catalog using the API
     */
    public function theExternalApplicationUpdatesACatalogUsingTheApi()
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
    public function theCatalogShouldBeUpdatedInThePim()
    {
        $catalog = $this->queryBus->execute(new GetCatalogQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c'));

        Assert::assertNotNull($catalog);
        Assert::assertEquals('Store US [NEW]', $catalog->getName());
    }
}
