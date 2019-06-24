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

namespace Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

interface AssetExistsInterface
{
    public function withIdentifier(AssetIdentifier $assetIdentifier): bool;

    public function withAssetFamilyAndCode(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $code): bool;

    public function withCode(AssetCode $code): bool;
}
