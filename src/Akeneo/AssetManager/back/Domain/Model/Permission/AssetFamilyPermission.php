<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\Permission;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyPermission
{
    private const ASSET_FAMILY_IDENTIFIER = 'asset_family_identifier';
    private const PERMISSIONS = 'permissions';

    private AssetFamilyIdentifier $assetFamilyIdentifier;

    /** @var UserGroupPermission[] */
    private array $permissions;

    private function __construct(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $permissions
    ) {
        Assert::allIsInstanceOf($permissions, UserGroupPermission::class);
        $this->assertUniquePermissions($permissions);

        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->permissions = $permissions;
    }

    /**
     * @param UserGroupPermission $permissions
     */
    private function assertUniquePermissions(array $permissions): void
    {
        $userGroup = [];
        foreach ($permissions as $permission) {
            $userGroupIdentifier = $permission->getUserGroupIdentifier()->normalize();
            if (in_array($userGroupIdentifier, $userGroup)) {
                throw new \InvalidArgumentException(
                    sprintf('Permission for user group %s already exists', $userGroupIdentifier)
                );
            }
            $userGroup[] = $userGroupIdentifier;
        }
    }

    /**
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     * @param UserGroupPermission[]     $permissions
     */
    public static function create(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $permissions
    ): self {
        return new self($assetFamilyIdentifier, $permissions);
    }

    public function normalize(): array
    {
        return [
            self::ASSET_FAMILY_IDENTIFIER => $this->assetFamilyIdentifier->normalize(),
            self::PERMISSIONS                 => array_map(fn (UserGroupPermission $userGroupPermission) => $userGroupPermission->normalize(), $this->permissions),
        ];
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }

    /**
     * @return UserGroupPermission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param UserGroupIdentifier $userGroupIdentifiers
     */
    public function isAllowedToEdit(array $userGroupIdentifiers): bool
    {
        Assert::allIsInstanceOf($userGroupIdentifiers, UserGroupIdentifier::class);
        if ($this->areAllUserGroupsAllowedToEdit()) {
            return true;
        }
        $userGroupPermissions = $this->findPermissionsByUserGroupIdentifiers($userGroupIdentifiers);

        return $this->hasEditPermission($userGroupPermissions);
    }

    /**
     * If there are no permissions set for the asset family (at its creation for instance), it means that every
     * user is allowed to edit.
     */
    private function areAllUserGroupsAllowedToEdit(): bool
    {
        return empty($this->permissions);
    }

    /**
     * @param UserGroupIdentifier $userGroupIdentifiers
     *
     * @return UserGroupPermission[]
     */
    private function findPermissionsByUserGroupIdentifiers(array $userGroupIdentifiers): array
    {
        return array_filter(
            $this->permissions,
            function (UserGroupPermission $userGroupPermission) use ($userGroupIdentifiers) {
                foreach ($userGroupIdentifiers as $userGroupIdentifier) {
                    if ($userGroupPermission->getUserGroupIdentifier()->equals($userGroupIdentifier)) {
                        return true;
                    }
                }

                return false;
            }
        );
    }

    /**
     * @param UserGroupPermission[] $userGroupPermissions
     */
    private function hasEditPermission(array $userGroupPermissions): bool
    {
        $editPermissions = array_filter(
            $userGroupPermissions,
            fn (UserGroupPermission $userGroupPermission) => $userGroupPermission->isAllowedToEdit()
        );

        return !empty($editPermissions);
    }
}
