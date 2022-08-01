<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoEnterprise\Test\Pim\Enrichment\Category\Integration\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;

final class SqlGetViewableCategoriesIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_returns_category_codes_that_can_be_read(): void
    {
        $this->createCategory(['code' => 'category_a']);
        $this->createCategory(['code' => 'category_b']);
        $this->createCategory(['code' => 'category_c']);

        $userGroup1 = $this->createUserGroup('group1');
        $userGroup2 = $this->createUserGroup('group2');
        $userGroup3 = $this->createUserGroup('group3');

        $userWithAllGroups = $this->createUser('user_with_all_groups', [$userGroup1, $userGroup2, $userGroup3]);
        $userWithGroup1And2 = $this->createUser('user_group12', [$userGroup1, $userGroup2]);
        $userWithGroup1 = $this->createUser('user_group1', [$userGroup1]);
        $userWithGroup2 = $this->createUser('user_group2', [$userGroup2]);
        $userWithGroup3 = $this->createUser('user_group3', [$userGroup3]);
        $userWithoutGroup = $this->createUser('user_without_group', []);

        // By default 'All' group is granted for all categories.
        $this->get(UserGroupCategoryPermissionsSaver::class)->save('All', [
            'own' => ['all' => false, 'identifiers' => []],
            'edit' => ['all' => false, 'identifiers' => []],
            'view' => ['all' => false, 'identifiers' => []],
        ]);
        $this->get(UserGroupCategoryPermissionsSaver::class)->save($userGroup1->getName(), [
            'own' => ['all' => false, 'identifiers' => []],
            'edit' => ['all' => false, 'identifiers' => []],
            'view' => ['all' => false, 'identifiers' => ['category_a']],
        ]);
        $this->get(UserGroupCategoryPermissionsSaver::class)->save($userGroup2->getName(), [
            'own' => ['all' => false, 'identifiers' => []],
            'edit' => ['all' => false, 'identifiers' => []],
            'view' => ['all' => false, 'identifiers' => ['category_c']],
        ]);

        /** @var GetViewableCategories $query */
        $query = $this->get(GetViewableCategories::class);

        Assert::assertEqualsCanonicalizing(
            [],
            $query->forUserId([], $userWithGroup1And2->getId())
        );
        Assert::assertEqualsCanonicalizing(
            ['category_a', 'category_c'],
            $query->forUserId(['category_a', 'category_b', 'category_c'], $userWithGroup1And2->getId())
        );
        Assert::assertEqualsCanonicalizing(
            ['category_a'],
            $query->forUserId(['category_a', 'category_b', 'category_c'], $userWithGroup1->getId())
        );
        Assert::assertEqualsCanonicalizing(
            ['category_c'],
            $query->forUserId(['category_a', 'category_b', 'category_c'], $userWithGroup2->getId())
        );
        Assert::assertEqualsCanonicalizing(
            [],
            $query->forUserId(['category_a', 'category_b', 'category_c'], $userWithGroup3->getId())
        );
        Assert::assertEqualsCanonicalizing(
            ['category_a'],
            $query->forUserId(['category_a'], $userWithAllGroups->getId())
        );
        Assert::assertEqualsCanonicalizing(
            [],
            $query->forUserId([], $userWithoutGroup->getId())
        );
        Assert::assertEqualsCanonicalizing(
            [],
            $query->forUserId(['category_a', 'category_b', 'category_c'], $userWithoutGroup->getId())
        );
    }

    private function createUserGroup(string $name): Group
    {
        $userGroup = new Group();
        $userGroup->setName($name);

        $violations = $this->get('validator')->validate($userGroup);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_user.saver.group')->save($userGroup);

        return $userGroup;
    }

    /**
     * @param string $username
     * @param Group[] $groups
     * @return User
     */
    private function createUser(string $username, array $groups): User
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }
}
