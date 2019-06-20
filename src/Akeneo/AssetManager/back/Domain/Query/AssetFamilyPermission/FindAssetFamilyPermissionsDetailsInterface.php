<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\AssetFamilyPermission;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindAssetFamilyPermissionsDetailsInterface
{
    /**
     * @return PermissionDetails[]
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array;
}
