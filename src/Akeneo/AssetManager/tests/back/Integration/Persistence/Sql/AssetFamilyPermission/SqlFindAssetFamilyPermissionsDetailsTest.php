<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\AssetFamilyPermission;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\Permission\AssetFamilyPermission;
use Akeneo\AssetManager\Domain\Model\Permission\RightLevel;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupPermission;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\FindAssetFamilyPermissionsDetailsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\PermissionDetails;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAssetFamilyPermissionsDetailsTest extends SqlIntegrationTestCase
{
    private FindAssetFamilyPermissionsDetailsInterface $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneoassetmanager.infrastructure.persistence.query.find_asset_family_permissions_details');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_the_asset_family_permissions_details()
    {
        $this->loadFixtures();
        $permissions = $this->query->find(AssetFamilyIdentifier::fromString('designer'));
        $this->assertPermissions(
            [
                ['user_group_identifier' => 10, 'user_group_name' => 'Catalog Manager', 'right_level' => 'edit'],
                ['user_group_identifier' => 11, 'user_group_name' => 'IT support', 'right_level' => 'view'],
            ],
            $permissions
        );
    }

    /**
     * @test
     */
    public function it_sets_a_default_right_level_for_new_user_groups()
    {
        $this->loadFixtures();
        $this->createNewUserGroup();
        $permissions = $this->query->find(AssetFamilyIdentifier::fromString('designer'));
        $this->assertPermissions(
            [
                ['user_group_identifier' => 10, 'user_group_name' => 'Catalog Manager', 'right_level' => 'edit'],
                ['user_group_identifier' => 11, 'user_group_name' => 'IT support', 'right_level' => 'view'],
                ['user_group_identifier' => 12, 'user_group_name' => 'New user group', 'right_level' => 'view'],
            ],
            $permissions
        );
    }

    /**
     * @test
     */
    public function it_sets_a_default_right_level_for_new_user_groups_when_there_are_no_permissions_set()
    {
        $this->createNewUserGroup();
        $permissions = $this->query->find(AssetFamilyIdentifier::fromString('designer'));
        $this->assertPermissions(
            [
                ['user_group_identifier' => 12, 'user_group_name' => 'New user group', 'right_level' => 'edit'],
            ],
            $permissions
        );
    }

    /**
     * @test
     */
    public function it_finds_no_permissions_details()
    {
        $permissions = $this->query->find(AssetFamilyIdentifier::fromString('brand'));
        $this->assertEmpty($permissions);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')
            ->create(
                AssetFamily::create(
                    AssetFamilyIdentifier::fromString('designer'),
                    [],
                    Image::createEmpty(),
                    RuleTemplateCollection::empty()
                )
            );
        $insertFakeGroups = <<<SQL
 INSERT INTO oro_access_group (id, name)
 VALUES
    (10, 'Catalog Manager'),
    (11, 'IT support');
SQL;
        $this->get('database_connection')->executeUpdate($insertFakeGroups);

        $assetFamilyPermission = AssetFamilyPermission::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(11),
                    RightLevel::fromString('view')
                ),
            ]
        );

        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family_permission')->save($assetFamilyPermission);
    }

    private function assertPermissions(
        array $expectedNormalizedPermissionsDetails,
        array $actualPermissionsDetails
    ): void {
        $actualNormalizedPermissionDetails = array_map(
            fn (PermissionDetails $permissionDetails) => $permissionDetails->normalize(),
            $actualPermissionsDetails
        );
        $this->assertEquals($expectedNormalizedPermissionsDetails, $actualNormalizedPermissionDetails);
    }

    private function createNewUserGroup()
    {
        $insertNewUserGroup = <<<SQL
 INSERT INTO oro_access_group (id, name)
 VALUES
    (12, 'New user group');
SQL;
        $this->get('database_connection')->executeUpdate($insertNewUserGroup);
    }
}
