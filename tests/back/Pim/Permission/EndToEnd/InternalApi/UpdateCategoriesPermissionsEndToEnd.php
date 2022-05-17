<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 */
class UpdateCategoriesPermissionsEndToEnd extends WebTestCase
{
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->getContainer()->get('database_connection');
    }

    public function testUpdateAttributeGroupPermissionsWithNonDefaultTypeUserGroup()
    {
        $this->get('feature_flags')->enable('permission');
        $this->authenticateAsAdmin();

        $group = $this->createUserGroupWillAllPermissionsByDefault('TestApp', 'app');
        $rootCategory = $this->createCategory(['code' => 'foo']);
        $childCategory = $this->createCategory(['code' => 'bar', 'parent' => 'foo']);

        $this->assertUserGroupHasAllPermissionsOnCategory($group, $rootCategory);
        $this->assertUserGroupHasAllPermissionsOnCategory($group, $childCategory);

        $this->fetchAndSubmitCategoryForm($rootCategory);

        $this->assertUserGroupHasAllPermissionsOnCategory($group, $rootCategory);
        $this->assertUserGroupHasAllPermissionsOnCategory($group, $childCategory);
    }

    private function assertUserGroupHasAllPermissionsOnCategory(Group $group, Category $category): void
    {
        $expectedPermissions = [
            'view' => true,
            'edit' => true,
            'own' => true,
        ];

        $actualPermissions = $this->getCategoryPermissions($group->getId(), $category->getId());

        Assert::assertSame($expectedPermissions, $actualPermissions);
    }

    private function fetchAndSubmitCategoryForm(Category $category): void
    {
        $url = sprintf('/enrich/product-category-tree/%s/edit', $category->getId());

        $this->client->request('GET', $url);

        $form = json_decode($this->client->getResponse()->getContent(), true)['form'];
        $values = $this->parseRecursiveFormValues($form);
        parse_str(http_build_query($values), $query);

        $this->client->request(
            'POST',
            $url,
            $query,
            [],
            [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            ],
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    private function parseRecursiveFormValues($leaf, $values = []): array
    {
        if (!is_array($leaf)) {
            return $values;
        }

        if (isset($leaf['fullName'])) {
            $key = $leaf['fullName'];

            if (str_ends_with($key, '[]')) {
                $key = substr($key, 0, -2);
            }

            $values[$key] = $leaf['value'];

            return $values;
        }

        foreach ($leaf as $child) {
            $values = array_merge($values, $this->parseRecursiveFormValues($child, $values));
        }

        return $values;
    }

    private function createUserGroupWillAllPermissionsByDefault(string $name, string $type = Group::TYPE_DEFAULT): Group
    {
        /** @var SaverInterface $userGroupSaver */
        $userGroupSaver = $this->get('pim_user.saver.group');

        $userGroup = new Group($name);
        $userGroup->setType($type);
        $userGroup->setDefaultPermissions([
            'category_view' => true,
            'category_edit' => true,
            'category_own' => true,
        ]);

        $userGroupSaver->save($userGroup);

        return $userGroup;
    }

    /**
     * @return array{view: bool, edit: bool, own: bool}|null
     */
    private function getCategoryPermissions(int $userGroupId, int $categoryId): ?array
    {
        $query = <<<SQL
SELECT 
   view_items AS view,
   edit_items AS edit,
   own_items AS own
FROM pimee_security_product_category_access
WHERE user_group_id = :user_group_id
AND category_id = :category_id
SQL;

        $permissions = $this->connection->fetchAssociative($query, [
            'user_group_id' => $userGroupId,
            'category_id' => $categoryId,
        ]);

        if (!$permissions) {
            return null;
        }

        return array_map(fn($v) => (bool) $v, $permissions);
    }
}
