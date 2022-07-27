<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class AddDefaultPermissionsToRootCategoryIntegration extends TestCase
{
    private Connection $connection;
    private GroupRepository $groupRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = self::getContainer()->get('database_connection');
        $this->groupRepository = self::getContainer()->get('pim_user.repository.group');
    }

    public function testDefaultUserGroupHasPermissionsOnNewRootCategoriesByDefault()
    {
        /** @var Group $defaultUserGroup */
        $defaultUserGroup = $this->groupRepository->getDefaultUserGroup();
        assert($defaultUserGroup !== null);

        $category = $this->createRootCategory('foo');

        $permissions = $this->getCategoryPermissions($defaultUserGroup->getId(), $category->getId());
        $this->assertEquals([
            'view' => true,
            'edit' => true,
            'own' => true,
        ], $permissions);
    }

    /**
     * @dataProvider permissions
     */
    public function testUserGroupHasExpectedPermissionsOnNewRootCategoriesByDefault(
        array $defaultPermissions,
        array $expectedPermissions
    ) {
        $userGroup = $this->createUserGroup('foo', $defaultPermissions);
        $category = $this->createRootCategory('foo');

        $permissions = $this->getCategoryPermissions(
            $userGroup->getId(),
            $category->getId()
        );
        $this->assertEquals($expectedPermissions, $permissions);
    }

    public function permissions(): array
    {
        return [
            [
                [
                    'category_view' => true,
                    'category_edit' => true,
                    'category_own' => true,
                ],
                [
                    'view' => true,
                    'edit' => true,
                    'own' => true,
                ],
            ],
            [
                [
                    'category_view' => true,
                    'category_edit' => true,
                    'category_own' => false,
                ],
                [
                    'view' => true,
                    'edit' => true,
                    'own' => false,
                ],
            ],
            [
                [
                    'category_view' => true,
                    'category_edit' => false,
                    'category_own' => false,
                ],
                [
                    'view' => true,
                    'edit' => false,
                    'own' => false,
                ],
            ],
        ];
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

        $permissions = $this->connection->fetchAssoc($query, [
            'user_group_id' => $userGroupId,
            'category_id' => $categoryId,
        ]);

        if (!$permissions) {
            return null;
        }

        return array_map(fn($v) => (bool) $v, $permissions);
    }

    private function createUserGroup(string $name, array $defaultPermissions): Group
    {
        $userGroup = new Group();
        $userGroup->setName($name);
        $userGroup->setDefaultPermissions($defaultPermissions);

        /** @var EntityManagerInterface $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($userGroup);
        $em->flush();

        return $userGroup;
    }

    private function createRootCategory(string $code): Category
    {
        $category = new Category();
        $category->setCode($code);

        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
