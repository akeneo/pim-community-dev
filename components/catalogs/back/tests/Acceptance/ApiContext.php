<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    /**
     * @When the external application creates a catalog using the API
     */
    public function theExternalApplicationCreatesACatalogUsingTheApi()
    {
        $this->response = $this->kernel->handle(
            Request::create(
                uri: '/api/rest/v1/catalogs',
                method: 'POST',
                server: [
                    'Content-Type' => 'application/json',
                ],
                content: json_encode([
                    'name' => 'My first catalog',
                ]),
            )
        );

        Assert::assertEquals(201, $this->response->getStatusCode());
    }

    /**
     * @Then the PIM should store the catalog
     */
    public function thePimShouldStoreTheCatalog()
    {
        throw new PendingException();
    }

    /**
     * @Then the response should contain the catalog id
     */
    public function theResponseShouldContainTheCatalogId()
    {
        throw new PendingException();
    }
}
