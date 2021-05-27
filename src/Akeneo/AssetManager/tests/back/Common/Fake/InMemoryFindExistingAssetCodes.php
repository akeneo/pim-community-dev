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

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindExistingAssetCodesInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindExistingAssetCodes implements FindExistingAssetCodesInterface
{
    private InMemoryAssetRepository $assetRepository;

    public function __construct(InMemoryAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): array
    {
        $existingAssets = $this->assetRepository->getByAssetFamilyAndCodes($assetFamilyIdentifier, $assetCodes);
        $existingCodes = array_map(fn(Asset $asset) => $asset->getCode(), $existingAssets);

        return array_filter($assetCodes, fn($code) => in_array($code, $existingCodes));
    }
}
