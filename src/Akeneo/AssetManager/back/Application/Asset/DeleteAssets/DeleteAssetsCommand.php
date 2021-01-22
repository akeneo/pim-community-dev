<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\DeleteAssets;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAssetsCommand
{
    public string $assetFamilyIdentifier;
    public array $assetCodes;

    public function __construct(string $assetFamilyIdentifier, array $assetCodes)
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->assetCodes = $assetCodes;
    }
}
