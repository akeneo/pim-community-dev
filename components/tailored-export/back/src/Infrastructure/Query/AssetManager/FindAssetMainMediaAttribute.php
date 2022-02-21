<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAttributeAsMainMediaInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\MediaFileAsMainMedia as AssetManagerMediaFileAsMainMedia;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\MediaLinkAsMainMedia as AssetManagerMediaLinkAsMainMedia;
use Akeneo\Platform\TailoredExport\Domain\Query\AssetCollection\AttributeAsMainMedia;
use Akeneo\Platform\TailoredExport\Domain\Query\AssetCollection\FindAssetMainMediaAttributeInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\AssetCollection\MediaFileAsMainMedia;
use Akeneo\Platform\TailoredExport\Domain\Query\AssetCollection\MediaLinkAsMainMedia;

class FindAssetMainMediaAttribute implements FindAssetMainMediaAttributeInterface
{
    public function __construct(
        private GetAttributeAsMainMediaInterface $getAttributeAsMainMedia,
    ) {
    }

    public function forAssetFamily(string $assetFamilyCode): AttributeAsMainMedia
    {
        $attributeAsMainMedia = $this->getAttributeAsMainMedia->forAssetFamilyCode($assetFamilyCode);

        return match (true) {
            $attributeAsMainMedia instanceof AssetManagerMediaFileAsMainMedia => new MediaFileAsMainMedia(
                $attributeAsMainMedia->isScopable(),
                $attributeAsMainMedia->isLocalizable(),
            ),
            $attributeAsMainMedia instanceof AssetManagerMediaLinkAsMainMedia => new MediaLinkAsMainMedia(
                $attributeAsMainMedia->isScopable(),
                $attributeAsMainMedia->isLocalizable(),
                $attributeAsMainMedia->getPrefix(),
                $attributeAsMainMedia->getSuffix(),
            ),
            default => throw new \InvalidArgumentException('Unsupported attribute type as main media'),
        };
    }
}
