<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Connectivity\Connection\ServiceApi\Model\ConnectedAppWithValidToken;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppFactory;
use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticationContext implements Context
{
    private ContainerInterface $container;

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

    private function createConnectedApp(array $scopes = []): ConnectedAppWithValidToken
    {
        $connectedAppFactory = $this->container->get(ConnectedAppFactory::class);

        return $connectedAppFactory->createFakeConnectedAppWithValidToken(
            '11231759-a867-44b6-a36d-3ed7aeead51a',
            'shopifi',
            $scopes,
        );
    }

    private function logAs(string $username): void
    {
        $user = $this->container->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }

    public function createAccessToken(array $scopes = []): string
    {
        $connectedApp = $this->createConnectedApp($scopes);

        return $connectedApp->getAccessToken();
    }

    public function createAuthenticatedClient(array $scopes = []): KernelBrowser
    {
        $connectedApp = $this->createConnectedApp($scopes);

        /** @var KernelBrowser $client */
        $client = $this->container->get(KernelBrowser::class);
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $connectedApp->getAccessToken());

        // The connected user is not derivated from the access token when in `test` env
        // We need to explicitly log in with it
        $this->logAs($connectedApp->getUsername());

        return $client;
    }
}
