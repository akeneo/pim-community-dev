<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Catalogs\Application\Persistence\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Domain\Model\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\UpsertCatalogQuery;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
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

    public function __construct(
        private KernelInterface $kernel,
        private AuthenticationContext $authentication,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    /**
     * @When the external application creates a catalog using the API
     */
    public function theExternalApplicationCreatesACatalogUsingTheApi()
    {
        $client = $this->authentication->getAuthenticatedClient([
            'write_catalogs',
        ]);

        $client->request(
            method: 'POST',
            uri: '/api/rest/v1/catalogs',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: \json_encode([
                'name' => 'Store US',
            ]),
        );

        $this->response = $client->getResponse();

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

        $catalog = $this->container->get(FindOneCatalogByIdQueryInterface::class)
            ->execute($payload['id']);

        Assert::assertNotNull($catalog);
    }

    /**
     * @Given an existing catalog
     */
    public function anExistingCatalog()
    {
        $this->container->get(UpsertCatalogQuery::class)
            ->execute(
                Catalog::fromSerialized([
                    'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                    'name' => 'Store US',
                ])
            );
    }

    /**
     * @When the external application retrieves the catalog using the API
     */
    public function theExternalApplicationRetrievesTheCatalogUsingTheApi()
    {
        $client = $this->authentication->getAuthenticatedClient([
            'read_catalogs',
        ]);

        $client->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $this->response = $client->getResponse();

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
    }

    /**
     * @Given existing catalogs
     */
    public function existingCatalogs()
    {
        $query = $this->container->get(UpsertCatalogQuery::class);

        $query->execute(
            Catalog::fromSerialized([
                'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'name' => 'Store US',
            ])
        );
        $query->execute(
            Catalog::fromSerialized([
                'id' => 'd68b8b7c-74e2-43de-9444-838c5b420f07',
                'name' => 'Store FR',
            ])
        );
    }

    /**
     * @When the external application retrieves all catalogs using the API
     */
    public function theExternalApplicationRetrievesAllCatalogsUsingTheApi()
    {
        $client = $this->authentication->getAuthenticatedClient([
            'read_catalogs',
        ]);

        $client->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs',
        );

        $this->response = $client->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain all catalogs details
     */
    public function theResponseShouldContainAllCatalogsDetails()
    {
        $payload = \json_decode($this->response->getContent(), true);
        $items = $payload['_embedded']['items'];

        Assert::assertIsArray($items);
        Assert::assertCount(2, $items);
    }
}
