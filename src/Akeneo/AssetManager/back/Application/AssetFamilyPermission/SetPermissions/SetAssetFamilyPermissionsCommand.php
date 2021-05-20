<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions;

/**
 * This command represents all the user group permissions set for one asset family
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SetAssetFamilyPermissionsCommand
{
    public string $assetFamilyIdentifier;

    /** @var SetUserGroupPermissionCommand[] */
    public array $permissionsByUserGroup = [];

    public function __construct(string $assetFamilyIdentifier, array $permissionsByUserGroup)
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->permissionsByUserGroup = $permissionsByUserGroup;
    }
}
