<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\AttributeGroupFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\AttributeGroupPermissionsFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\UserGroupPermissionsFixturesLoader;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetUserGroupAttributeGroupsPermissionsActionEndToEnd extends WebTestCase
{
    private AttributeGroupFixturesLoader $attributeGroupFixturesLoader;
    private AttributeGroupPermissionsFixturesLoader $attributeGroupPermissionsFixturesLoader;
    private UserGroupPermissionsFixturesLoader $userGroupPermissionsFixturesLoader;
    private GroupRepositoryInterface $groupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeGroupFixturesLoader = $this->get('akeneo_integration_tests.loader.attribute_group');
        $this->attributeGroupPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.attribute_group_permissions');
        $this->userGroupPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.user_group_permissions');
        $this->groupRepository = $this->get('pim_user.repository.group');
    }

    public function testItReturnsUserGroupAttributeGroupPermissions(): void
    {
        $adminUser = $this->authenticateAsAdmin();
        $redactorUserGroup = $this->groupRepository->findOneByIdentifier('Redactor');
        $adminUser->addGroup($redactorUserGroup);

        $this->attributeGroupPermissionsFixturesLoader->revokeAttributeGroupPermissions('other');

        $this->attributeGroupFixturesLoader->createAttributeGroup(['code' => 'attribute_group_A']);
        $this->attributeGroupFixturesLoader->createAttributeGroup(['code' => 'attribute_group_B']);
        $this->attributeGroupFixturesLoader->createAttributeGroup(['code' => 'attribute_group_C']);

        $this->attributeGroupPermissionsFixturesLoader->givenTheRightOnAttributeGroupCodes(Attributes::VIEW_ATTRIBUTES, $redactorUserGroup, [
            'attribute_group_A',
            'attribute_group_B',
            'attribute_group_C',
        ]);
        $this->attributeGroupPermissionsFixturesLoader->givenTheRightOnAttributeGroupCodes(Attributes::EDIT_ATTRIBUTES, $redactorUserGroup, [
            'attribute_group_A',
            'attribute_group_C',
        ]);
        $this->userGroupPermissionsFixturesLoader->givenTheUserGroupDefaultPermissions($redactorUserGroup, [
            'attribute_group_edit' => false,
            'attribute_group_view' => true,
        ]);

        $this->client->request(
            'GET',
            '/rest/permissions/user-group/Redactor/attribute-group',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertSame([
            'edit' => [
                'all' => false,
                'identifiers' => ['attribute_group_A', 'attribute_group_C'],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ], $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
