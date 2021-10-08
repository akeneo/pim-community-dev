<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetUserGroupRootCategoriesAccesses;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\CategoryPermissionsFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\UserGroupPermissionsFixturesLoader;

class GetUserGroupRootCategoriesAccessesIntegration extends TestCase
{
    private GetUserGroupRootCategoriesAccesses $query;
    private CategoryPermissionsFixturesLoader $categoryPermissionsFixturesLoader;
    private UserGroupPermissionsFixturesLoader $userGroupPermissionsFixturesLoader;
    private GroupInterface $redactorUserGroup;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetUserGroupRootCategoriesAccesses::class);

        $this->categoryPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.category_permissions');
        $this->userGroupPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.user_group_permissions');

        $adminUser = $this->createAdminUser();
        $this->redactorUserGroup = $this->get('pim_user.repository.group')->findOneByIdentifier('redactor');
        $adminUser->addGroup($this->redactorUserGroup);

        $this->categoryPermissionsFixturesLoader->revokeCategoryPermissions('master');

        $this->createCategory(['code' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_A', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_B', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_C', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'b_tree']);
        $this->createCategory(['code' => 'b_tree_child_A', 'parent' => 'b_tree']);
    }

    public function categoryPermissionsDataProvider(): array
    {
        return [
            'test without permissions' => [
                'expected' => [
                    'own' => [
                        'all' => false,
                        'identifiers' => [],
                    ],
                    'edit' => [
                        'all' => false,
                        'identifiers' => [],
                    ],
                    'view' => [
                        'all' => false,
                        'identifiers' => [],
                    ],
                ],
                'userGroupDefaultPermissions' => [],
                'ownableCategories' => [],
                'editableCategories' => [],
                'viewableCategories' => [],
            ],
            'test it returns "all" flag and categories for each access level' => [
                'expected' => [
                    'own' => [
                        'all' => false,
                        'identifiers' => ['b_tree'],
                    ],
                    'edit' => [
                        'all' => false,
                        'identifiers' => ['master', 'b_tree'],
                    ],
                    'view' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                ],
                'userGroupDefaultPermissions' => [
                    'category_own' => false,
                    'category_edit' => false,
                    'category_view' => true,
                ],
                'ownableCategories' => [
                    'b_tree',
                ],
                'editableCategories' => [
                    'master',
                    'a_tree_child_A',
                    'b_tree',
                ],
                'viewableCategories' => [
                    'master',
                    'a_tree',
                    'a_tree_child_A',
                    'b_tree',
                    'b_tree_child_A',
                ],
            ],
        ];
    }

    /**
     * @dataProvider categoryPermissionsDataProvider
     */
    public function testItFetchesUserGroupRootCategoriesAccesses(
        array $expected,
        array $userGroupDefaultPermissions,
        array $ownableCategories,
        array $editableCategories,
        array $viewableCategories
    ): void
    {
        $this->categoryPermissionsFixturesLoader->givenTheRightOnCategoryCodes(Attributes::VIEW_ITEMS, $this->redactorUserGroup, $viewableCategories);
        $this->categoryPermissionsFixturesLoader->givenTheRightOnCategoryCodes(Attributes::EDIT_ITEMS, $this->redactorUserGroup, $editableCategories);
        $this->categoryPermissionsFixturesLoader->givenTheRightOnCategoryCodes(Attributes::OWN_PRODUCTS, $this->redactorUserGroup, $ownableCategories);
        $this->userGroupPermissionsFixturesLoader->givenTheUserGroupDefaultPermissions($this->redactorUserGroup, $userGroupDefaultPermissions);

        $results = $this->query->execute($this->redactorUserGroup->getName());

        $this->assertSame($expected, $results);
    }
}
