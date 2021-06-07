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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;

/**
 * Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAssetExists implements AssetExistsInterface
{
    private InMemoryAssetRepository $assetRepository;

    public function __construct(InMemoryAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    public function withIdentifier(AssetIdentifier $assetIdentifier): bool
    {
        return $this->assetRepository->hasAsset($assetIdentifier);
    }

    public function withAssetFamilyAndCode(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $code): bool
    {
        $hasAsset = true;
        try {
            $this->assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $code);
        } catch (AssetNotFoundException $exception) {
            $hasAsset = false;
        }

        return $hasAsset;
    }

    public function withCode(AssetCode $code): bool
    {
        foreach ($this->assetRepository->all() as $asset) {
            if ($asset->getCode()->equals($code)) {
                return true;
            }
        }

        return false;
    }
}
