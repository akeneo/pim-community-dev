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
use Akeneo\UserManagement\Component\Model\UserInterface;
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
    }

    public function testItListsTheUsers(): void
    {
        $this->createUserWithGroupsAndRoles('test1', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles('test2', ['Redactor'], ['ROLE_USER']);

        $users = ($this->get(SqlFindUsers::class))();

        Assert::assertCount(2, $users);
        Assert::containsOnlyInstancesOf(User::class);
    }

    public function testItFiltersTheUserOnUsername(): void
    {
        $this->createUserWithGroupsAndRoles('test1', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles('test2', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles('test3', ['Redactor'], ['ROLE_USER']);
        $this->createUserWithGroupsAndRoles('julia', ['Redactor'], ['ROLE_USER']);

        $user = ($this->get(SqlFindUsers::class))('julia');

        Assert::assertCount(1, $user);
        Assert::containsOnlyInstancesOf(User::class);
        Assert::assertEquals('julia', $user[0]->getUsername());
    }

    /**
    public function testItListsTheUserGroupsWithPagination(): void
    {
        $userGroups = ($this->get(SqlFindUserGroups::class))(
            null,
            null,
            2
        );

        Assert::assertCount(2, $userGroups);
        Assert::assertLessThan(2, $userGroups[0]->getId());
        Assert::assertEquals(2, $userGroups[1]->getId());

        $userGroups = ($this->get(SqlFindUserGroups::class))(
            null,
            2,
            2
        );

        Assert::assertCount(2, $userGroups);
        Assert::assertGreaterThan(2, $userGroups[0]->getId());
        Assert::assertGreaterThan(2, $userGroups[1]->getId());
    }
     **/
    private function createUserWithGroupsAndRoles(string $username, array $groupNames, array $stringRoles): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setId(uniqid());
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

        return $user;
    }

    private function clearUser()
    {
        $connection = $this->get('doctrine.dbal.connection');
        $sql = <<<SQL 
            TRUNCATE TABLE oro_user
        SQL;
        $connection->executeQuery($sql)->fetch();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
