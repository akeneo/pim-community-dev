<?php

namespace Akeneo\Test\IntegrationTestsBundle\Security;

use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SystemUserAuthenticator
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Create a token with a user system with all access.
     */
    public function createSystemUser()
    {
        $user = $this->container->get('pim_user.factory.user')->create();
        $user->setUsername('system');
        $groups = $this->container->get('pim_user.repository.group')->findAll();

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->container->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }
}
