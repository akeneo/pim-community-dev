<?php

namespace Akeneo\Test\IntegrationTestsBundle\Security;

use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SystemUserAuthenticator
{
    /** @var UserFactory */
    private $userFactory;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var RoleRepositoryInterface */
    private $roleRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        UserFactory $userFactory,
        GroupRepositoryInterface $groupRepository,
        RoleRepositoryInterface $roleRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->userFactory = $userFactory;
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Create a token with a user system with all access.
     */
    public function createSystemUser()
    {
        $user = $this->userFactory->create();
        $user->setUsername('system');
        $groups = $this->groupRepository->findAll();

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->roleRepository->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }
}
