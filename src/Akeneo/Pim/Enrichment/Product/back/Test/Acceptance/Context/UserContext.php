<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Test\Acceptance\User\InMemoryGroupRepository;
use Akeneo\Test\Acceptance\User\InMemoryRoleRepository;
use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserContext implements Context
{
    private int $userIdSequence = 0;

    public function __construct(
        private InMemoryRoleRepository $roleRepository,
        private InMemoryGroupRepository $groupRepository,
        private InMemoryUserRepository $userRepository,
        private UserFactory $userFactory,
        private ValidatorInterface $validator,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * @Given an authenticated user
     */
    public function anAuthenticatedUser(): void
    {
        /** @var UserInterface $user */
        $user = $this->userFactory->create();
        $user->setUsername('admin');

        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given the :username :userGroup user
     */
    public function theManagerUser(string $username, string $userGroup): void
    {
        $this->createUser($username, ['ROLE_ADMIN'], [$userGroup]);
    }

    /**
     * @Given /^the (.*) user groups$/
     */
    public function theUserGroups(string $groups): void
    {
        foreach (explode(',', $groups) as $groupName) {
            $group = new Group($groupName);
            $this->groupRepository->save($group);
        }
    }

    /**
     * @Given /^the (.*) roles$/
     */
    public function theRoles(string $roles): void
    {
        foreach (explode(',', $roles) as $stringRole) {
            $role = new Role($stringRole);
            $this->roleRepository->save($role);
        }
    }

    protected function createUser(string $username, array $stringRoles, array $groupNames): UserInterface
    {
        $user = $this->userFactory->create();
        $user->setUsername($username);
        $user->setPassword('password');
        $user->setEmail($username . '@example.com');
        $user->setId(++$this->userIdSequence);

        $roles = $this-> roleRepository->findAll();
        foreach ($roles as $role) {
            if (\in_array($role->getRole(), $stringRoles)) {
                $user->addRole($role);
            }
        }

        $groups = $this->groupRepository->findAll();
        foreach ($groups as $group) {
            if (\in_array($group->getName(), $groupNames) || 'All' === $group->getName()) {
                $user->addGroup($group);
            }
        }

        $this->validator->validate($user);
        $this->userRepository->save($user);

        return $user;
    }
}
