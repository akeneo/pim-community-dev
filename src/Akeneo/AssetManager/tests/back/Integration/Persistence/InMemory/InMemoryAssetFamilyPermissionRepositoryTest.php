<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyPermissionRepository;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\AssetFamilyPermission;
use Akeneo\AssetManager\Domain\Model\Permission\RightLevel;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupPermission;
use PHPUnit\Framework\TestCase;

class InMemoryAssetFamilyPermissionRepositoryTest extends TestCase
{
    private InMemoryAssetFamilyPermissionRepository $inMemoryAssetFamilyPermissionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->inMemoryAssetFamilyPermissionRepository = new InMemoryAssetFamilyPermissionRepository();
    }

    /**
     * @test
     */
    public function it_saves_an_asset_family_permission()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $userGroupIdentifier = UserGroupIdentifier::fromInteger(12);
        $rightLevel = RightLevel::fromString('view');
        $assetFamilyPermission = AssetFamilyPermission::create(
            $assetFamilyIdentifier,
            [UserGroupPermission::create($userGroupIdentifier, $rightLevel)]
        );

        $this->inMemoryAssetFamilyPermissionRepository->save($assetFamilyPermission);

        $this->assertTrue($this->inMemoryAssetFamilyPermissionRepository->hasPermission(
            $assetFamilyIdentifier,
            $userGroupIdentifier,
            $rightLevel
        ));

        $this->assertFalse($this->inMemoryAssetFamilyPermissionRepository->hasPermission(
            $assetFamilyIdentifier,
            $userGroupIdentifier,
            RightLevel::fromString('edit')
        ));
    }

    /**
     * @test
     */
    public function it_returns_the_asset_family()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $expectedAssetFamilyPermission = AssetFamilyPermission::create(
            $assetFamilyIdentifier,
            [UserGroupPermission::create(UserGroupIdentifier::fromInteger(12), RightLevel::fromString('view'))]
        );
        $this->inMemoryAssetFamilyPermissionRepository->save($expectedAssetFamilyPermission);

        $actualAssetFamilyPermission = $this->inMemoryAssetFamilyPermissionRepository->getByAssetFamilyIdentifier($assetFamilyIdentifier);

        $this->assertEquals($expectedAssetFamilyPermission, $actualAssetFamilyPermission);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_asset_family_permission_if_it_has_none()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $actualAssetFamilyPermission = $this->inMemoryAssetFamilyPermissionRepository->getByAssetFamilyIdentifier($assetFamilyIdentifier);
        $expectedAssetFamilyPermission = AssetFamilyPermission::create($assetFamilyIdentifier, []);

        $this->assertEquals($expectedAssetFamilyPermission->normalize(), $actualAssetFamilyPermission->normalize());
    }
}
