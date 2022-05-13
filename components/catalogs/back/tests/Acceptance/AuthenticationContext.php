<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Connectivity\Connection\PublicApi\Service\ConnectedAppFactory;
use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticationContext implements Context
{
    private ContainerInterface $container;
    private array $tokens = [];

    public function __construct(
        KernelInterface $kernel,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    /**
     * @BeforeScenario
     */
    public function clear()
    {
        $this->tokens = [];
    }

    public function getAccessToken(array $scopes = []): string
    {
        $key = \serialize($scopes);

        if (isset($this->tokens[$key])) {
            return $this->tokens[$key];
        }

        $connectedAppFactory = $this->container->get(ConnectedAppFactory::class);
        $connectedApp = $connectedAppFactory->createFakeConnectedAppWithValidToken(
            '11231759-a867-44b6-a36d-3ed7aeead51a',
            'shopifi',
            $scopes,
        );

        $this->tokens[$key] = $connectedApp->getAccessToken();

        return $connectedApp->getAccessToken();
    }

    public function getAuthenticatedClient(array $scopes = []): KernelBrowser
    {
        $token = $this->getAccessToken($scopes);

        /** @var KernelBrowser $client */
        $client = $this->container->get(KernelBrowser::class);

        $client->setServerParameter('AUTHORIZATION', 'Bearer ' . $token);

        return $client;
    }
}
