<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class AddDefaultPermissionsToAttributeGroupIntegration extends TestCase
{
    private Connection $connection;
    private GroupRepository $groupRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = self::getContainer()->get('database_connection');
        $this->groupRepository = self::getContainer()->get('pim_user.repository.group');
    }

    public function testDefaultUserGroupHasPermissionsOnNewAttributeGroupsByDefault()
    {
        /** @var Group $defaultUserGroup */
        $defaultUserGroup = $this->groupRepository->getDefaultUserGroup();
        assert($defaultUserGroup !== null);

        $attributeGroup = $this->createAttributeGroup('foo');

        $permissions = $this->getAttributeGroupPermissions($defaultUserGroup->getId(), $attributeGroup->getId());
        $this->assertEquals([
            'view' => true,
            'edit' => true,
        ], $permissions);
    }

    /**
     * @dataProvider permissions
     *
     * @param array<string, bool> $defaultPermissions
     * @param array<string, bool> $expectedPermissions
     */
    public function testUserGroupHasExpectedPermissionsOnNewAttributeGroupsByDefault(
        array $defaultPermissions,
        array $expectedPermissions
    ) {
        $userGroup = $this->createUserGroup('foo', $defaultPermissions);
        $attributeGroup = $this->createAttributeGroup('foo');

        $permissions = $this->getAttributeGroupPermissions(
            $userGroup->getId(),
            $attributeGroup->getId()
        );
        $this->assertEquals($expectedPermissions, $permissions);
    }

    public function permissions(): array
    {
        return [
            'all permissions are given by default' => [
                [
                    'attribute_group_view' => true,
                    'attribute_group_edit' => true,
                ],
                [
                    'view' => true,
                    'edit' => true,
                ],
            ],
            'only the view permission is given by default' => [
                [
                    'attribute_group_view' => true,
                    'attribute_group_edit' => false,
                ],
                [
                    'view' => true,
                    'edit' => false,
                ],
            ],
        ];
    }

    /**
     * @return array{view: bool, edit: bool}|null
     */
    private function getAttributeGroupPermissions(int $userGroupId, int $attributeGroupId): ?array
    {
        $query = <<<SQL
SELECT 
   view_attributes AS view,
   edit_attributes AS edit
FROM pimee_security_attribute_group_access
WHERE user_group_id = :user_group_id
AND attribute_group_id = :attribute_group_id
SQL;

        $permissions = $this->connection->fetchAssoc($query, [
            'user_group_id' => $userGroupId,
            'attribute_group_id' => $attributeGroupId,
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

    private function createAttributeGroup(string $code): AttributeGroup
    {
        $attributeGroup = new AttributeGroup();
        $attributeGroup->setCode($code);
        $attributeGroup->setCreated(new \DateTime());

        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        return $attributeGroup;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
