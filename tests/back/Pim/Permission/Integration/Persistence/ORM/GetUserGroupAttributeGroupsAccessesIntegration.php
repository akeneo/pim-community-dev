<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetUserGroupAttributeGroupsAccesses;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\AttributeGroupFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\AttributeGroupPermissionsFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\UserGroupPermissionsFixturesLoader;

class GetUserGroupAttributeGroupsAccessesIntegration extends TestCase
{
    private GetUserGroupAttributeGroupsAccesses $query;
    private AttributeGroupFixturesLoader $attributeGroupFixturesLoader;
    private AttributeGroupPermissionsFixturesLoader $attributeGroupPermissionsFixturesLoader;
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

        $this->query = $this->get(GetUserGroupAttributeGroupsAccesses::class);

        $this->attributeGroupFixturesLoader = $this->get('akeneo_integration_tests.loader.attribute_group');
        $this->attributeGroupPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.attribute_group_permissions');
        $this->userGroupPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.user_group_permissions');

        $adminUser = $this->createAdminUser();
        $this->redactorUserGroup = $this->get('pim_user.repository.group')->findOneByIdentifier('redactor');
        $adminUser->addGroup($this->redactorUserGroup);

        $this->attributeGroupPermissionsFixturesLoader->revokeAttributeGroupPermissions('other');

        $this->attributeGroupFixturesLoader->createAttributeGroup(['code' => 'attribute_group_A']);
        $this->attributeGroupFixturesLoader->createAttributeGroup(['code' => 'attribute_group_B']);
        $this->attributeGroupFixturesLoader->createAttributeGroup(['code' => 'attribute_group_C']);
    }

    public function attributeGroupPermissionsDataProvider(): array
    {
        return [
            'test without permissions' => [
                'expected' => [
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
                'viewableAttributeGroups' => [],
                'editableAttributeGroups' => [],
            ],
            'test it returns "all" flag and attribute groups for each access level' => [
                'expected' => [
                    'edit' => [
                        'all' => false,
                        'identifiers' => ['attribute_group_A', 'attribute_group_C'],
                    ],
                    'view' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                ],
                'userGroupDefaultPermissions' => [
                    'attribute_group_edit' => false,
                    'attribute_group_view' => true,
                ],
                'editableAttributeGroups' => [
                    'attribute_group_A',
                    'attribute_group_C',
                ],
                'viewableAttributeGroups' => [
                    'attribute_group_A',
                    'attribute_group_B',
                    'attribute_group_C',
                ],
            ],
        ];
    }

    /**
     * @dataProvider attributeGroupPermissionsDataProvider
     */
    public function testItFetchesUserGroupAttributeGroupsAccesses(
        array $expected,
        array $userGroupDefaultPermissions,
        array $editableAttributeGroups,
        array $viewableAttributeGroups
    ): void {
        $this->attributeGroupPermissionsFixturesLoader->givenTheRightOnAttributeGroupCodes(Attributes::VIEW_ATTRIBUTES, $this->redactorUserGroup, $viewableAttributeGroups);
        $this->attributeGroupPermissionsFixturesLoader->givenTheRightOnAttributeGroupCodes(Attributes::EDIT_ATTRIBUTES, $this->redactorUserGroup, $editableAttributeGroups);
        $this->userGroupPermissionsFixturesLoader->givenTheUserGroupDefaultPermissions($this->redactorUserGroup, $userGroupDefaultPermissions);

        $results = $this->query->execute($this->redactorUserGroup->getName());

        $this->assertSame($expected, $results);
    }
}
