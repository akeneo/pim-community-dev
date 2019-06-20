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

namespace Akeneo\AssetManager\Domain\Repository;

use Akeneo\AssetManager\Domain\Model\Permission\AssetFamilyPermission;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface AssetFamilyPermissionRepositoryInterface
{
    public function getByAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyPermission): AssetFamilyPermission;

    public function save(AssetFamilyPermission $assetFamilyPermission): void;
}
