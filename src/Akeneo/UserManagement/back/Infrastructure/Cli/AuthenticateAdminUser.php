<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Infrastructure\Cli;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Bundle\Security\SystemUserToken;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This service was initially a part of AuthenticateCommandAsAdminUserListener
 * Its main purpose is to isolate the authentication details, to be able to make this service lazy
 * So it will be instantiated only if needed (and so its dependencies)
 */
class AuthenticateAdminUser
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectRepository $groupRepository,
        private readonly ObjectRepository $roleRepository,
        private readonly SimpleFactoryInterface $userFactory,
    ) {
    }

    public function __invoke(): void
    {
        try {
            $user = $this->userFactory->create();
            $user->setUsername(UserInterface::SYSTEM_USER_NAME);

            $groups = $this->groupRepository->findAll();
            foreach ($groups as $group) {
                $user->addGroup($group);
            }

            $roles = $this->roleRepository->findAll();
            foreach ($roles as $role) {
                $user->addRole($role);
            }

            $token = new SystemUserToken($user);
            $this->tokenStorage->setToken($token);
        } catch (DBALException $e) {
            // do nothing.
            // An exception can happen if db does not exist yet for instance
        }
    }
}
