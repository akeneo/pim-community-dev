<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Catalogs\Application\Persistence\FindOneCatalogByIdQueryInterface;
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
        KernelInterface $kernel,
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
}
