<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 */
class UpdateAttributeGroupPermissionsEndToEnd extends WebTestCase
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

        $this->createUserGroup('Some Connected App user group', 'app');

        $this->createAttributeGroups(['attribute1']);

        $this->client->request(
            'POST',
            '/rest/attribute-group/attribute1',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'attributes' => [],
                'attributes_sort_order' => [],
                'labels' => [],
                'code' => 'attribute1',
                'sort_order' => 101,
                'permissions' => [
                    'edit' => [],
                    'view' => [
                        'Redactor',
                    ],
                ],
            ])
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $attribute1Permissions = $this->getAttributeGroupPermissions('attribute1');

        assert::assertSame(
            [
                'Redactor' => [
                    'view' => true,
                    'edit' => false,
                ],
                'Some Connected App user group' => [
                    'view' => true,
                    'edit' => true,
                ],
            ],
            $attribute1Permissions
        );
    }

    private function createUserGroup(string $name, string $type = Group::TYPE_DEFAULT): void
    {
        /** @var SaverInterface $userGroupSaver */
        $userGroupSaver = $this->get('pim_user.saver.group');

        $userGroup = new Group($name);
        $userGroup->setType($type);
        $userGroup->setDefaultPermissions([
            'attribute_group_view' => true,
            'attribute_group_edit' => true,
        ]);

        $userGroupSaver->save($userGroup);
    }

    private function createAttributeGroups(array $codes): void
    {
        $factory = $this->get('pim_catalog.factory.attribute_group');
        $attributeGroupUpdater = $this->get('pim_catalog.updater.attribute_group');
        $validator = $this->get('validator');
        $attributeGroups = [];
        foreach ($codes as $code) {
            $attributeGroup = $factory->create();
            $attributeGroupUpdater->update($attributeGroup, ['code' => $code]);
            $constraints = $validator->validate($attributeGroup);
            $this->assertCount(0, $constraints, (string) $constraints);
            $attributeGroups[] = $attributeGroup;
        }

        $this->get('pim_catalog.saver.attribute_group')->saveAll($attributeGroups);
    }

    private function getAttributeGroupPermissions(string $attributeGroupCode): array
    {
        $query = <<<SQL
SELECT oag.name AS userGroupName, view_attributes AS view, edit_attributes AS edit
FROM pimee_security_attribute_group_access as ag_access
JOIN oro_access_group oag on ag_access.user_group_id = oag.id
JOIN pim_catalog_attribute_group attribute_group on ag_access.attribute_group_id = attribute_group.id
WHERE attribute_group.code = :ag_code
ORDER BY userGroupName ASC
SQL;

        $permissionsByUserGroup = $this->connection->fetchAllAssociativeIndexed($query, [
            'ag_code' => $attributeGroupCode,
        ]);

        return array_map(function (array $permissionByUserGroup) {
            return array_map(fn ($permission) => (bool) $permission, $permissionByUserGroup);
        }, $permissionsByUserGroup);
    }
}
