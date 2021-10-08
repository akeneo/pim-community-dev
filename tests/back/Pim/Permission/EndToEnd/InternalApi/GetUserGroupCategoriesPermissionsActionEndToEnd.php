<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\CategoryPermissionsFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\UserGroupPermissionsFixturesLoader;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetUserGroupCategoriesPermissionsActionEndToEnd extends WebTestCase
{
    private CategoryPermissionsFixturesLoader $categoryPermissionsFixturesLoader;
    private UserGroupPermissionsFixturesLoader $userGroupPermissionsFixturesLoader;
    private GroupRepositoryInterface $groupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.category_permissions');
        $this->userGroupPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.user_group_permissions');
        $this->groupRepository = $this->get('pim_user.repository.group');
    }

    public function testItReturnsUserGroupCategoryPermissions(): void
    {
        $adminUser = $this->authenticateAsAdmin();
        $redactorUserGroup = $this->groupRepository->findOneByIdentifier('Redactor');
        $adminUser->addGroup($redactorUserGroup);

        $this->categoryPermissionsFixturesLoader->revokeCategoryPermissions('master');

        $this->createCategory(['code' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_A', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_B', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_C', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'b_tree']);
        $this->createCategory(['code' => 'b_tree_child_A', 'parent' => 'b_tree']);

        $this->categoryPermissionsFixturesLoader->givenTheRightOnCategoryCodes(Attributes::VIEW_ITEMS, $redactorUserGroup, [
            'master',
            'a_tree',
            'a_tree_child_A',
            'b_tree',
            'b_tree_child_A',
        ]);
        $this->categoryPermissionsFixturesLoader->givenTheRightOnCategoryCodes(Attributes::EDIT_ITEMS, $redactorUserGroup, [
            'master',
            'a_tree_child_A',
            'b_tree',
        ]);
        $this->categoryPermissionsFixturesLoader->givenTheRightOnCategoryCodes(Attributes::OWN_PRODUCTS, $redactorUserGroup, ['b_tree']);
        $this->userGroupPermissionsFixturesLoader->givenTheUserGroupDefaultPermissions($redactorUserGroup, [
            'category_own' => false,
            'category_edit' => false,
            'category_view' => true,
        ]);

        $this->client->request(
            'GET',
            '/rest/permissions/user-group/Redactor/category',
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
