<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\IndexAssets;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAssetsByAssetFamilyCommand
{
    /** string $assetFamily */
    public string $assetFamilyIdentifier;

    public function __construct(string $assetFamilyIdentifier)
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
    }
}
