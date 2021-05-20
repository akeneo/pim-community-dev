<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\AssetFamilyPermission;
use Akeneo\AssetManager\Domain\Model\Permission\RightLevel;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupPermission;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyPermissionRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SetAssetFamilyPermissionsHandler
{
    private AssetFamilyPermissionRepositoryInterface $repository;

    public function __construct(AssetFamilyPermissionRepositoryInterface $assetFamilyPermissionRepository)
    {
        $this->repository = $assetFamilyPermissionRepository;
    }

    public function __invoke(SetAssetFamilyPermissionsCommand $command)
    {
        $permissions = [];
        foreach ($command->permissionsByUserGroup as $permissionByUserGroup) {
            $permissions[] = UserGroupPermission::create(
                UserGroupIdentifier::fromInteger($permissionByUserGroup->userGroupIdentifier),
                RightLevel::fromString($permissionByUserGroup->rightLevel)
            );
        }

        $assetFamilyPermissions = AssetFamilyPermission::create(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
            $permissions
        );

        $this->repository->save($assetFamilyPermissions);
    }
}
