<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Exception;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;

class AssetAlreadyExistError extends UserFacingError
{
    public static function fromAsset(Asset $asset): self
    {
        return new self(
            'pim_asset_manager.asset.validation.code.should_be_unique',
            [
                '%code%' => $asset->getCode()->normalize(),
            ]
        );
    }
}
