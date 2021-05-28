<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\AssetFamilyPermission;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class PermissionDetails
{
    public int $userGroupIdentifier;

    public string $userGroupName;

    public string $rightLevel;

    public function normalize()
    {
        return [
            'user_group_identifier' => $this->userGroupIdentifier,
            'user_group_name'       => $this->userGroupName,
            'right_level'           => $this->rightLevel,
        ];
    }
}
