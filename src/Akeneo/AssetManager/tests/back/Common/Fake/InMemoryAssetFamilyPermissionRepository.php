<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\AssetFamilyPermission;
use Akeneo\AssetManager\Domain\Model\Permission\RightLevel;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyPermissionRepositoryInterface;

class InMemoryAssetFamilyPermissionRepository implements AssetFamilyPermissionRepositoryInterface
{
    /** @var AssetFamilyPermission[] */
    private ?array $assetFamilyPermissions = null;

    public function save(AssetFamilyPermission $assetFamilyPermission): void
    {
        $refEntityIdentifier = $assetFamilyPermission->getAssetFamilyIdentifier()->normalize();
        $this->assetFamilyPermissions[$refEntityIdentifier] = $assetFamilyPermission;
    }

    public function getByAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier
    ): AssetFamilyPermission {
        return $this->assetFamilyPermissions[(string) $assetFamilyIdentifier] ?? AssetFamilyPermission::create(
                $assetFamilyIdentifier,
                []
            );
    }

    public function hasPermission(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        UserGroupIdentifier $userGroupIdentifier,
        RightLevel $rightLevel
    ): bool {
        $refEntityIdentifier = $assetFamilyIdentifier->normalize();

        if (array_key_exists($refEntityIdentifier, $this->assetFamilyPermissions)) {
            $assetFamilyPermission = $this->assetFamilyPermissions[$refEntityIdentifier];
            $normalized = $assetFamilyPermission->normalize();

            foreach ($normalized['permissions'] as $normalizedPermission) {
                if (
                    $normalizedPermission['user_group_identifier'] === $userGroupIdentifier->normalize()
                    && $normalizedPermission['right_level'] === $rightLevel->normalize()
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
