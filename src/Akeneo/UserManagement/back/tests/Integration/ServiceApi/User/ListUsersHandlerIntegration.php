<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\UserManagement\Integration\ServiceApi\User;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\ServiceApi\User\User;
use Akeneo\UserManagement\ServiceApi\User\ListUsersHandlerInterface;
use Akeneo\UserManagement\ServiceApi\User\UsersQuery;
use PHPUnit\Framework\Assert;

final class ListUsersHandlerIntegration extends TestCase
{
    private RoleRepositoryInterface $roleRepository;
    private GroupRepositoryInterface $groupRepository;
    private LocaleRepositoryInterface $localeRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleRepository = $this->get('pim_user.repository.role');
        $this->groupRepository = $this->get('pim_user.repository.group');
        $this->localeRepository = $this->get('pim_catalog.repository.locale');

        $this->createUserWithGroupsAndRoles(1, 'test1', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles(2, 'test2', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles(3, 'test3', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles(4, 'julia', ['Redactor'], ['ROLE_USER']);
    }

    public function testItListsTheUsers(): void
    {
        $users = $this->getHandler()->fromQuery(new UsersQuery());

        Assert::assertCount(4, $users);
        Assert::containsOnlyInstancesOf(User::class);
    }

    public function testItFiltersTheUsersOnUsername(): void
    {
        $users = $this->getHandler()->fromQuery(new UsersQuery('julia'));

        Assert::assertCount(1, $users);
        Assert::containsOnlyInstancesOf(User::class);
        Assert::assertEquals('julia', $users[0]->getUsername());
    }

    public function testItListsTheUsersWithPagination(): void
    {
        $users = $this->getHandler()->fromQuery(new UsersQuery(limit: 2));

        Assert::assertCount(2, $users);
        $lastId = $users[1]->getId();

        $users = $this->getHandler()->fromQuery(new UsersQuery(
            searchAfterId: $lastId,
            limit: 2
        ));

        Assert::assertCount(2, $users);
        Assert::assertGreaterThan($lastId, $users[0]->getId());
    }

    private function createUserWithGroupsAndRoles(int $id, string $username, array $groupNames, array $stringRoles): void
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setId($id);
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');
        $user->setUILocale($this->localeRepository->findOneByIdentifier('de_DE'));
        $user->setCatalogLocale($this->localeRepository->findOneByIdentifier('en_US'));

        $groups = $this->groupRepository->findAll();
        foreach ($groups as $group) {
            if (in_array($group->getName(), $groupNames)) {
                $user->addGroup($group);
            }
        }

        $roles = $this->roleRepository->findAll();
        foreach ($roles as $role) {
            if (in_array($role->getRole(), $stringRoles)) {
                $user->addRole($role);
            }
        }

        $this->get('pim_user.saver.user')->save($user);
    }

    private function getHandler(): ListUsersHandlerInterface
    {
        return $this->get(ListUsersHandlerInterface::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
