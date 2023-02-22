<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Bundle\Security\SystemUserToken;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Create a user system if token is null on CLI command "pim" or "akeneo"
 * This listener is called before each command.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserSystemListener
{
    private array $commandsThatNeedUserSystem = [];

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectRepository $groupRepository,
        private readonly ObjectRepository $roleRepository,
        private readonly SimpleFactoryInterface $userFactory
    ) {
    }

    public function registerCommand(Command $command): void
    {
        $commandName = $command::getDefaultName();
        if (null !== $commandName) {
            $this->commandsThatNeedUserSystem[] = $command::getDefaultName();
        }
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function createUserSystem(ConsoleCommandEvent $event): void
    {
        if (!\in_array($event->getCommand()->getName(), $this->commandsThatNeedUserSystem)) {
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
