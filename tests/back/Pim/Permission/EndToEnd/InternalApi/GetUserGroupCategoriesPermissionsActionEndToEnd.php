<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetUserGroupCategoriesPermissionsActionEndToEnd extends WebTestCase
{
    private GroupRepositoryInterface $groupRepository;
    private CategoryAccessManager $categoryAccessManager;
    private ObjectManager $objectManager;
    private ProductCategoryRepositoryInterface $productCategoryRepository;
    private SaverInterface $userGroupSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->get('pim_user.repository.group');
        $this->categoryAccessManager = $this->get('pimee_security.manager.category_access');
        $this->objectManager = $this->get('doctrine.orm.default_entity_manager');
        $this->productCategoryRepository = $this->get('pim_catalog.repository.product_category');
        $this->userGroupSaver = $this->get('pim_user.saver.group');
    }

    public function testItReturnsUserGroupCategoryPermissions(): void
    {
        $adminUser = $this->authenticateAsAdmin();
        $redactorUserGroup = $this->groupRepository->findOneByIdentifier('redactor');
        $adminUser->addGroup($redactorUserGroup);

        $masterCategory = $this->productCategoryRepository->findOneByIdentifier('master');
        $this->categoryAccessManager->revokeAccess($masterCategory);
        $this->objectManager->flush($masterCategory);

        $this->createCategory(['code' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_A', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_B', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_C', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'b_tree']);
        $this->createCategory(['code' => 'b_tree_child_A', 'parent' => 'b_tree']);

        $this->givenTheRightOnCategoryCodes(Attributes::VIEW_ITEMS, $redactorUserGroup, [
            'master',
            'a_tree',
            'a_tree_child_A',
            'b_tree',
            'b_tree_child_A',
        ]);
        $this->givenTheRightOnCategoryCodes(Attributes::EDIT_ITEMS, $redactorUserGroup, [
            'master',
            'a_tree_child_A',
            'b_tree',
        ]);
        $this->givenTheRightOnCategoryCodes(Attributes::OWN_PRODUCTS, $redactorUserGroup, ['b_tree']);
        $this->givenTheUserGroupDefaultPermissions($redactorUserGroup, [
            'category_own' => false,
            'category_edit' => false,
            'category_view' => true,
        ]);

        $this->client->request(
            'GET',
            '/rest/permissions/user-group/redactor/category',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $result = json_decode($this->client->getResponse()->getContent(), true);

        /* expected result :
         * [
         *     'own' => [
         *         'all' => false,
         *         'identifiers' => ['b_tree'],
         *     ],
         *     'edit' => [
         *         'all' => false,
         *         'identifiers' => ['master', 'b_tree'],
         *     ],
         *     'view' => [
         *         'all' => true,
         *         'identifiers' => [],
         *     ],
         * ];
         */
        Assert::assertCount(3, $result);

        Assert::assertArrayHasKey('own', $result);
        Assert::assertIsArray($result['own']);
        Assert::assertCount(2, $result['own']);
        Assert::assertArrayHasKey('all', $result['own']);
        Assert::assertFalse($result['own']['all']);
        Assert::assertArrayHasKey('identifiers', $result['own']);
        Assert::assertEqualsCanonicalizing(['b_tree'], $result['own']['identifiers']);

        Assert::assertArrayHasKey('edit', $result);
        Assert::assertIsArray($result['edit']);
        Assert::assertCount(2, $result['edit']);
        Assert::assertArrayHasKey('all', $result['edit']);
        Assert::assertFalse($result['edit']['all']);
        Assert::assertArrayHasKey('identifiers', $result['edit']);
        Assert::assertEqualsCanonicalizing(['master', 'b_tree'], $result['edit']['identifiers']);

        Assert::assertArrayHasKey('view', $result);
        Assert::assertIsArray($result['view']);
        Assert::assertCount(2, $result['view']);
        Assert::assertArrayHasKey('all', $result['view']);
        Assert::assertTrue($result['view']['all']);
        Assert::assertArrayHasKey('identifiers', $result['view']);
        Assert::assertIsArray($result['view']['identifiers']);
        Assert::assertCount(0, $result['view']['identifiers']);
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
     * @param array {
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
