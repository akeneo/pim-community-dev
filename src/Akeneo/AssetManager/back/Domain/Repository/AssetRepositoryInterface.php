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

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

interface AssetRepositoryInterface
{
    public function create(Asset $asset): void;

    public function update(Asset $asset): void;

    /**
     * @throws AssetNotFoundException
     */
    public function getByIdentifier(AssetIdentifier $identifier): Asset;


    /**
     * @throws AssetNotFoundException
     */
    public function getByAssetFamilyAndCode(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $code): Asset;

    /**
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     * @param AssetCode[] $assetCodes
     */
    public function deleteByAssetFamilyAndCodes(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): void;

    /**
     * @throws AssetNotFoundException
     */
    public function deleteByAssetFamilyAndCode(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $code): void;

    public function count(): int;

    public function countByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int;

    public function nextIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $code): AssetIdentifier;
}
