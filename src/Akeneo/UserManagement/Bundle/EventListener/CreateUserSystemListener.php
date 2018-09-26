<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Bundle\Security\SystemUserToken;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\DBALException;
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
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObjectRepository */
    private $groupRepository;

    /** @var ObjectRepository */
    private $roleRepository;

    /** @var SimpleFactoryInterface */
    private $userFactory;

    /**
     * @param TokenStorageInterface  $tokenStorage
     * @param ObjectRepository       $groupRepository
     * @param ObjectRepository       $roleRepository
     * @param SimpleFactoryInterface $userFactory
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectRepository $groupRepository,
        ObjectRepository $roleRepository,
        SimpleFactoryInterface $userFactory
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
        $this->userFactory = $userFactory;
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function createUserSystem(ConsoleCommandEvent $event): void
    {
        if (0 === preg_match('#^pim|akeneo#', $event->getCommand()->getName())) {
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
