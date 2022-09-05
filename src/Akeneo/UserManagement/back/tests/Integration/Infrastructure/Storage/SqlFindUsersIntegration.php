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

namespace Akeneo\Test\UserManagement\Integration\Infrastructure\Storage;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Domain\Model\User;
use Akeneo\UserManagement\Infrastructure\Storage\SqlFindUsers;
use PHPUnit\Framework\Assert;

final class SqlFindUsersIntegration extends TestCase
{
    private RoleRepositoryInterface $roleRepository;
    private GroupRepositoryInterface $groupRepository;
    private LocaleRepositoryInterface $localeRepository;

    private int $userTest1Id;
    private int $userTest3Id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleRepository = $this->get('pim_user.repository.role');
        $this->groupRepository = $this->get('pim_user.repository.group');
        $this->localeRepository = $this->get('pim_catalog.repository.locale');

        $this->userTest1Id = $this->createUserWithGroupsAndRoles('test1', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles('test2', ['Redactor'], ['ROLE_USER']);
        $this->userTest3Id = $this->createUserWithGroupsAndRoles('test3', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles('julia', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles('marie', ['IT support'], ['ROLE_USER']);
    }

    public function testItListsTheUsers(): void
    {
        $users = $this->getQuery()();

        $this->assertUsersEqual(['test1', 'test2', 'test3', 'julia', 'marie'], $users);
    }

    public function testItFiltersTheUserOnUsername(): void
    {
        $users = $this->getQuery()('julia');

        $this->assertUsersEqual(['julia'], $users);
    }

    public function testItFiltersTheUserOnUserId(): void
    {
        $users = $this->getQuery()(includeIds: [$this->userTest1Id, $this->userTest3Id]);

        $this->assertUsersEqual(['test1', 'test3'], $users);
    }

    public function testItFiltersTheUserOnUserGroupId(): void
    {
        $group = $this->groupRepository->findOneByIdentifier('Redactor');
        $users = $this->getQuery()(includeGroupIds: [$group->getId()]);

        $this->assertUsersEqual(['test1', 'test2', 'test3', 'julia'], $users);
    }

    public function testItListsTheUserWithPagination(): void
    {
        $users = $this->getQuery()(limit: 2);

        Assert::assertCount(2, $users);
        $lastId = $users[1]->getId();

        $users = $this->getQuery()(searchAfterId: $lastId, limit: 2);

        Assert::assertCount(2, $users);
        Assert::assertGreaterThan($lastId, $users[0]->getId());
    }

    private function getQuery(): SqlFindUsers
    {
        return $this->get(SqlFindUsers::class);
    }

    private function createUserWithGroupsAndRoles(string $username, array $groupNames, array $stringRoles): int
    {
        $user = $this->get('pim_user.factory.user')->create();
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

        return $user->getId();
    }

    private function assertUsersEqual(array $expectedUsernames, array $actualUsers): void
    {
        $actualUserNames = array_map(static fn(User $actualUser) => $actualUser->getUsername(), $actualUsers);

        Assert::assertEquals($expectedUsernames, $actualUserNames);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
