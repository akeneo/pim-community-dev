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
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamilyPermission\SqlFindAssetFamilyWhereUserGroupIsLastToHaveEditRight;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAssetFamilyWhereUserGroupIsLastToHaveEditRightTest extends SqlIntegrationTestCase
{
    private SqlFindAssetFamilyWhereUserGroupIsLastToHaveEditRight $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneoassetmanager.infrastructure.persistence.query.find_asset_family_where_user_group_is_last_to_have_edit_right');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_finds_the_asset_families_the_user_group_is_the_last_to_have_edit_permission_on()
    {
        $assetFamilyIdentifiers = $this->query->find(10);
        $this->assertEquals(['color'], $assetFamilyIdentifiers);

        $assetFamilyIdentifiers = $this->query->find(11);
        $this->assertEquals([], $assetFamilyIdentifiers);

        $assetFamilyIdentifiers = $this->query->find(12);
        $this->assertEquals(['city'], $assetFamilyIdentifiers);

        $assetFamilyIdentifiers = $this->query->find(13);
        $this->assertEquals([], $assetFamilyIdentifiers);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $designer = AssetFamily::create(AssetFamilyIdentifier::fromString('designer'), [], Image::createEmpty(), RuleTemplateCollection::empty());
        $color = AssetFamily::create(AssetFamilyIdentifier::fromString('color'), [], Image::createEmpty(), RuleTemplateCollection::empty());
        $brand = AssetFamily::create(AssetFamilyIdentifier::fromString('brand'), [], Image::createEmpty(), RuleTemplateCollection::empty());
        $city = AssetFamily::create(AssetFamilyIdentifier::fromString('city'), [], Image::createEmpty(), RuleTemplateCollection::empty());

        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')->create($designer);
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')->create($color);
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')->create($brand);
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')->create($city);

        $insertFakeUserGroups = <<<SQL
 INSERT INTO oro_access_group (id, name)
 VALUES
    (10, 'Catalog Manager'),
    (11, 'Administrator'),
    (12, 'Security Agent'),
    (13, 'IT support');
SQL;
        $this->get('database_connection')->executeUpdate($insertFakeUserGroups);

        /**
         *  Here is the permission fixture set:
         *
         *      DESIGNER
         *          EDIT = 10, 11, 12
         *          VIEW = 13
         *      COLOR
         *          EDIT = 10
         *          VIEW = 11, 12, 13
         *      CITY
         *          EDIT = 12
         *          VIEW = 10, 11, 13
         *      BRAND
         *          no permission set
         */

        $assetFamilyPermission = AssetFamilyPermission::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(11),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(12),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(13),
                    RightLevel::fromString('view')
                ),
            ]
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family_permission')
            ->save($assetFamilyPermission);

        $assetFamilyPermission = AssetFamilyPermission::create(
            AssetFamilyIdentifier::fromString('color'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(11),
                    RightLevel::fromString('view')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(12),
                    RightLevel::fromString('view')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(13),
                    RightLevel::fromString('view')
                ),
            ]
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family_permission')
            ->save($assetFamilyPermission);

        $assetFamilyPermission = AssetFamilyPermission::create(
            AssetFamilyIdentifier::fromString('city'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('view')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(11),
                    RightLevel::fromString('view')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(12),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(13),
                    RightLevel::fromString('view')
                ),
            ]
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family_permission')
            ->save($assetFamilyPermission);
    }
}
