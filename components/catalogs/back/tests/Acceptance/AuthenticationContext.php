<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Connectivity\Connection\ServiceApi\Model\ConnectedAppWithValidToken;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppFactory;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
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
    private ?UserInterface $adminUser = null;

    public function __construct(
        KernelInterface $kernel,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    public function getAdminUser(): UserInterface
    {
        return $this->adminUser ??= $this->createAdminUser();
    }

    private function createAdminUser(): UserInterface
    {
        $user = $this->container->get('pim_user.factory.user')->create();
        $user->setUsername('admin');
        $user->setPlainPassword('admin');
        $user->setEmail('admin@example.com');
        $user->setSalt('E1F53135E559C253');
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $this->container->get('pim_user.manager')->updatePassword($user);

        $adminRole = $this->container->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        if (null !== $adminRole) {
            $user->addRole($adminRole);
        }

        $userRole = $this->container->get('pim_user.repository.role')->findOneByIdentifier(User::ROLE_DEFAULT);
        if (null !== $userRole) {
            $user->removeRole($userRole);
        }

        $this->container->get('pim_user.saver.user')->save($user);

        return $user;
    }

    public function createConnectedApp(array $scopes = []): ConnectedAppWithValidToken
    {
        $connectedAppFactory = $this->container->get(ConnectedAppFactory::class);

        $connectedApp = $connectedAppFactory->createFakeConnectedAppWithValidToken(
            '11231759-a867-44b6-a36d-3ed7aeead51a',
            'shopifi',
            $scopes,
        );

        $this->addAllPermissionsUserGroup('app_shopifi');

        return $connectedApp;
    }

    public function createAuthenticatedClient(ConnectedAppWithValidToken $connectedApp): KernelBrowser
    {
        /** @var KernelBrowser $client */
        $client = $this->container->get(KernelBrowser::class);
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $connectedApp->getAccessToken());

        return $client;
    }

    public function logAs(string $username): void
    {
        $user = $this->container->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }

    private function addAllPermissionsUserGroup(string $group): void
    {
        $this->callPermissionsSaver(
            service: 'Akeneo\Pim\Permission\Bundle\Saver\UserGroupAttributeGroupPermissionsSaver',
            group: $group,
            permissions: [
                'edit' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => true,
                    'identifiers' => [],
                ],
            ]
        );
        $this->callPermissionsSaver(
            service: 'Akeneo\Pim\Permission\Bundle\Saver\UserGroupLocalePermissionsSaver',
            group: $group,
            permissions: [
                'edit' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => true,
                    'identifiers' => [],
                ],
            ]
        );
        $this->callPermissionsSaver(
            service: 'Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver',
            group: $group,
            permissions: [
                'own' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'edit' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => true,
                    'identifiers' => [],
                ],
            ]
        );
    }

    private function callPermissionsSaver(string $service, string $group, array $permissions): void
    {
        if ($this->container->has($service)) {
            $this->container->get($service)->save($group, $permissions);
        }
    }
}
