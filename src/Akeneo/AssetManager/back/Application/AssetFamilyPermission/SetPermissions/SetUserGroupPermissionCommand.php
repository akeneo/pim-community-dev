<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions;

/**
 * This command represent a permission set for a user group
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SetUserGroupPermissionCommand
{
    public int $userGroupIdentifier;

    public string $rightLevel;

    public function __construct(int $userGroupIdentifier, string $rightLevel)
    {
        $this->userGroupIdentifier = $userGroupIdentifier;
        $this->rightLevel = $rightLevel;
    }
}
