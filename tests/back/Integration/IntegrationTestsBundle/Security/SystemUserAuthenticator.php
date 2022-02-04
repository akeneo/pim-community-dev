<?php

namespace Akeneo\Test\IntegrationTestsBundle\Security;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SystemUserAuthenticator
{
    public function __construct(
        private UserFactory $userFactory,
        private GroupRepositoryInterface $groupRepository,
        private RoleRepositoryInterface $roleRepository,
        private TokenStorageInterface $tokenStorage,
        private UserRepositoryInterface $userRepository,
        private SaverInterface $userSaver
    ) {
    }

    /**
     * Create a token with a user system with all access.
     */
    public function createSystemUser()
    {
        $user = $this->userRepository->findOneByIdentifier('system');
        if (null === $user) {
            $user = $this->userFactory->create();
            $user->setUsername('system');
            $user->setEmail('system@example.com');
            $user->setPassword('password');
        }

        $groups = $this->groupRepository->findAll();
        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->roleRepository->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->userSaver->save($user);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }
}
