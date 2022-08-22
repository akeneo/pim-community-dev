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

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Domain\Model\User;
use Akeneo\UserManagement\Infrastructure\Storage\SqlFindUsers;
use PHPUnit\Framework\Assert;

final class SqlFindUsersIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->get('pim_user.repository.user');
        $this->roleRepository = $this->get('pim_user.repository.role');
        $this->localeRepository = $this->get('pim_catalog.repository.locale');

        $this->createUserWithGroupsAndRoles(1, 'test1', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles(2, 'test2', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles(3, 'test3', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles(4, 'julia', ['Redactor'], ['ROLE_USER']);
    }

    public function testItListsTheUsers(): void
    {
        $users = ($this->get(SqlFindUsers::class))();

        Assert::assertCount(4, $users);
        Assert::containsOnlyInstancesOf(User::class);
    }

    public function testItFiltersTheUserOnUsername(): void
    {
        $user = ($this->get(SqlFindUsers::class))('julia');

        Assert::assertCount(1, $user);
        Assert::containsOnlyInstancesOf(User::class);
        Assert::assertEquals('julia', $user[0]->getUsername());
    }

    public function testItListsTheUserWithPagination(): void
    {
        $users = ($this->get(SqlFindUsers::class))(
            null,
            null,
            2
        );

        Assert::assertCount(2, $users);
        $lastId = $users[1]->getId();

        $users = ($this->get(SqlFindUsers::class))(
            null,
            $lastId,
            2
        );

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

        $groups = $this->get('pim_user.repository.group')->findAll();
        foreach ($groups as $group) {
            if (in_array($group->getName(), $groupNames)) {
                $user->addGroup($group);
            }
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            if (in_array($role->getRole(), $stringRoles)) {
                $user->addRole($role);
            }
        }

        $this->get('pim_user.saver.user')->save($user);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
