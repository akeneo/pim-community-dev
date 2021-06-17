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
use Akeneo\AssetManager\Domain\Repository\AssetFamilyPermissionRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\DBALException;
use PHPUnit\Framework\Assert;

class SqlAssetFamilyPermissionRepositoryTest extends SqlIntegrationTestCase
{
    private AssetFamilyPermissionRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family_permission');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_saves_and_returns_an_asset_family_permission()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyPermission = AssetFamilyPermission::create(
            $assetFamilyIdentifier,
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

        $this->repository->save($assetFamilyPermission);
        $assetFamilyPermission = $this->repository->getByAssetFamilyIdentifier($assetFamilyIdentifier);

        $this->assertEquals($assetFamilyPermission->normalize(), [
            'asset_family_identifier' => 'designer',
            'permissions'                 => [
                [
                    'user_group_identifier' => 10,
                    'right_level'           => 'edit',
                ],
                [
                    'user_group_identifier' => 11,
                    'right_level'           => 'view',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_returns_an_asset_family_permission_with_no_user_group_permission_when_there_are_no_permissions_set_for_it()
    {
        $assetFamilyPermission = $this->repository->getByAssetFamilyIdentifier(
            AssetFamilyIdentifier::fromString('designer')
        );

        $this->assertEquals($assetFamilyPermission->normalize(), [
            'asset_family_identifier' => 'designer',
            'permissions'                 => [],
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_permission_for_an_asset_family_that_does_not_exist()
    {
        $assetFamilyPermission = AssetFamilyPermission::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('edit')
                ),
            ]
        );

        $this->expectException(DBALException::class);
        $this->repository->save($assetFamilyPermission);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_permission_for_a_user_group_that_does_not_exist()
    {
        $assetFamilyPermission = AssetFamilyPermission::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(999),
                    RightLevel::fromString('edit')
                ),
            ]
        );

        $this->expectException(DBALException::class);
        $this->repository->save($assetFamilyPermission);
    }

    private function thereShouldBePermissionsInDatabase(array $expectedRows): void
    {
        $stmt = $this->get('database_connection')
            ->executeQuery('SELECT * FROM akeneo_asset_manager_asset_family_permissions;');
        $actualRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        Assert::assertJsonStringEqualsJsonString(
            json_encode($expectedRows),
            json_encode($actualRows),
            'There should be permissions in the database, but some rows weren\'t found.'
        );
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
    (10, 'TEST GROUP'),
    (11, 'TEST GROUP 2');
SQL;
        $this->get('database_connection')->executeUpdate($insertFakeGroups);
    }
}
