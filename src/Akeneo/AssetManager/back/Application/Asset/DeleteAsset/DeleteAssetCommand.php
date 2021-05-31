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

namespace Akeneo\AssetManager\Application\Asset\DeleteAsset;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAssetCommand
{
    public string $assetCode;

    public string $assetFamilyIdentifier;

    public function __construct(string $assetCode, string $assetFamilyIdentifier)
    {
        $this->assetCode = $assetCode;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
    }
}
