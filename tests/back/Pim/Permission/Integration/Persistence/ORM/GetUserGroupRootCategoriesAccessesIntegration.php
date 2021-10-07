<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetUserGroupRootCategoriesAccesses;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Persistence\ObjectManager;

class GetUserGroupRootCategoriesAccessesIntegration extends TestCase
{
    private GetUserGroupRootCategoriesAccesses $query;
    private CategoryAccessManager $categoryAccessManager;
    private ObjectManager $objectManager;
    private ProductCategoryRepositoryInterface $productCategoryRepository;
    private GroupInterface $redactorUserGroup;
    private SaverInterface $userGroupSaver;

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

        $this->categoryAccessManager = $this->get('pimee_security.manager.category_access');
        $this->objectManager = $this->get('doctrine.orm.default_entity_manager');
        $this->productCategoryRepository = $this->get('pim_catalog.repository.product_category');
        $this->userGroupSaver = $this->get('pim_user.saver.group');

        $adminUser = $this->createAdminUser();
        $this->redactorUserGroup = $this->get('pim_user.repository.group')->findOneByIdentifier('redactor');
        $adminUser->addGroup($this->redactorUserGroup);

        $masterCategory = $this->productCategoryRepository->findOneByIdentifier('master');
        $this->categoryAccessManager->revokeAccess($masterCategory);
        $this->objectManager->flush($masterCategory);

        $this->createCategory(['code' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_A', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_B', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_C', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'b_tree']);
        $this->createCategory(['code' => 'b_tree_child_A', 'parent' => 'b_tree']);
    }

    public function categoryHighestAccessLevelDataProvider(): array
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
                'viewableCategories' => [],
                'editableCategories' => [],
                'ownableCategories' => [],
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
                'viewableCategories' => [
                    'master',
                    'a_tree',
                    'a_tree_child_A',
                    'b_tree',
                    'b_tree_child_A',
                ],
                'editableCategories' => [
                    'master',
                    'a_tree_child_A',
                    'b_tree',
                ],
                'ownableCategories' => [
                    'b_tree',
                ],
            ],
        ];
    }

    /**
     * @dataProvider categoryHighestAccessLevelDataProvider
     */
    public function testItFetchesUserGroupRootCategoriesAccesses(
        array $expected,
        array $userGroupDefaultPermissions,
        array $viewableCategories,
        array $editableCategories,
        array $ownableCategories
    ): void
    {
        $this->givenTheRightOnCategoryCodes(Attributes::VIEW_ITEMS, $this->redactorUserGroup, $viewableCategories);
        $this->givenTheRightOnCategoryCodes(Attributes::EDIT_ITEMS, $this->redactorUserGroup, $editableCategories);
        $this->givenTheRightOnCategoryCodes(Attributes::OWN_PRODUCTS, $this->redactorUserGroup, $ownableCategories);
        $this->givenTheUserGroupDefaultPermissions($this->redactorUserGroup, $userGroupDefaultPermissions);

        $results = $this->query->execute($this->redactorUserGroup->getName());

        $this->assertSame($expected, $results);
    }

    /**
     * @param string[] $categoryCodes
     */
    private function givenTheRightOnCategoryCodes(string $accessLevel, GroupInterface $userGroup, array $categoryCodes): void
    {
        foreach ($categoryCodes as $categoryCode) {
            $category = $this->productCategoryRepository->findOneByIdentifier($categoryCode);

            $this->categoryAccessManager->revokeAccess($category);
            $this->objectManager->flush($category);

            $this->categoryAccessManager->grantAccess($category, $userGroup, $accessLevel);
        }
    }

    /**
     * @param array{
     *     category_own: bool,
     *     category_edit: bool,
     *     category_view: bool
     * } $defaultPermissions
     */
    private function givenTheUserGroupDefaultPermissions(GroupInterface $userGroup, array $defaultPermissions): void
    {
        foreach ($defaultPermissions as $permissionName => $flag) {
            $userGroup->setDefaultPermission($permissionName, $flag);
        }

        $this->userGroupSaver->save($userGroup);
    }
}
