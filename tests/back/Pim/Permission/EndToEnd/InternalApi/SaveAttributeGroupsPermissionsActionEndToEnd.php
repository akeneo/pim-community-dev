<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class SaveAttributeGroupsPermissionsActionEndToEnd extends WebTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = self::$container->get('database_connection');
    }

    public function testItSavesAttributeGroupsPermissions(): void
    {
        $this->get('feature_flags')->enable('permission');
        $this->authenticateAsAdmin();
        $this->createAttributeGroups(['marketing', 'technical']);

        $this->client->request(
            'POST',
            '/rest/permissions/attribute-group',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'user_group' => 'Redactor',
                'permissions' => [
                    'edit' => [
                        'all' => false,
                        'identifiers' => ['marketing', 'technical'],
                    ],
                    'view' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                ],
            ])
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $defaultPermissions = $this->getUserGroupDefaultPermissions('Redactor');
        assert::assertEquals([
            'attribute_group_edit' => false,
            'attribute_group_view' => true,
        ], $defaultPermissions);

        $marketingPermissions = $this->getAttributeGroupAccessByUserGroup('Redactor', 'marketing');
        $technicalPermissions = $this->getAttributeGroupAccessByUserGroup('Redactor', 'technical');
        $otherPermissions = $this->getAttributeGroupAccessByUserGroup('Redactor', 'other');
        assert::assertEquals([
            'edit' => true,
            'view' => true,
        ], $marketingPermissions);
        assert::assertEquals([
            'edit' => true,
            'view' => true,
        ], $technicalPermissions);
        assert::assertEquals([
            'edit' => false,
            'view' => true,
        ], $otherPermissions);
    }

    public function testItDoesNotSavesAttributeGroupsPermissionsWhenFeatureDisabled(): void
    {
        $this->get('feature_flags')->disable('permission');
        $this->authenticateAsAdmin();
        $this->createAttributeGroups(['marketing', 'technical']);

        $this->client->request(
            'POST',
            '/rest/permissions/attribute-group',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'user_group' => 'Redactor',
                'permissions' => [
                    'edit' => [
                        'all' => false,
                        'identifiers' => ['marketing', 'technical'],
                    ],
                    'view' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                ],
            ])
        );

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
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

    private function getUserGroupDefaultPermissions(string $name): array
    {
        $query = <<<SQL
SELECT default_permissions
FROM oro_access_group
WHERE name = :name
SQL;
        $result = $this->connection->fetchOne($query, [
            'name' => $name,
        ]);

        return \json_decode($result, true) ?? [];
    }

    private function getAttributeGroupAccessByUserGroup(string $userGroupName, string $attributeGroupCode): ?array
    {
        $query = <<<SQL
SELECT view_attributes AS view, edit_attributes AS edit
FROM pimee_security_attribute_group_access as ag_access
JOIN oro_access_group oag on ag_access.user_group_id = oag.id
JOIN pim_catalog_attribute_group attribute_group on ag_access.attribute_group_id = attribute_group.id
WHERE oag.name = :user_group_name
AND attribute_group.code = :ag_code
LIMIT 1
SQL;

        $permissions = $this->connection->fetchAssociative($query, [
            'user_group_name' => $userGroupName,
            'ag_code' => $attributeGroupCode,
        ]) ?: null;

        if (!$permissions) {
            return null;
        }

        return \array_map(fn ($permissionFlag) => (bool) $permissionFlag, $permissions);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
