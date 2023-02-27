<?php

namespace Akeneo\UserManagement\Infrastructure\Cli\EventListener;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Bundle\Security\SystemUserToken;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Infrastructure\Cli\Registry\AuthenticatedAsAdminCommandRegistry;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Create a user system for the commands that need to be authenticated as admin user before executing
 * This listener is called before each command.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticateCommandAsAdminUserListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectRepository $groupRepository,
        private readonly ObjectRepository $roleRepository,
        private readonly SimpleFactoryInterface $userFactory,
        private readonly AuthenticatedAsAdminCommandRegistry $commandRegistry,
    ) {
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function createUserSystem(ConsoleCommandEvent $event): void
    {
        if (!$this->commandRegistry->isCommandAuthenticatedAsAdminUser($event->getCommand()->getName())) {
            return;
        }

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
