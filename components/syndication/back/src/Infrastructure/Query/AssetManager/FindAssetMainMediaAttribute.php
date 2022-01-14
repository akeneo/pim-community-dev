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

namespace Akeneo\Platform\Syndication\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAttributeAsMainMediaInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\MediaFileAsMainMedia as AssetManagerMediaFileAsMainMedia;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\MediaLinkAsMainMedia as AssetManagerMediaLinkAsMainMedia;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\AttributeAsMainMedia;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\FindAssetMainMediaAttributeInterface;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\MediaFileAsMainMedia;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\MediaLinkAsMainMedia;

class FindAssetMainMediaAttribute implements FindAssetMainMediaAttributeInterface
{
    private GetAttributeAsMainMediaInterface $getAttributeAsMainMedia;

    public function __construct(GetAttributeAsMainMediaInterface $getAttributeAsMainMedia)
    {
        $this->getAttributeAsMainMedia = $getAttributeAsMainMedia;
    }

    public function forAssetFamily(string $assetFamilyCode): AttributeAsMainMedia
    {
        $attributeAsMainMedia = $this->getAttributeAsMainMedia->forAssetFamilyCode($assetFamilyCode);

        switch (true) {
            case $attributeAsMainMedia instanceof AssetManagerMediaFileAsMainMedia:
                return new MediaFileAsMainMedia(
                    $attributeAsMainMedia->isScopable(),
                    $attributeAsMainMedia->isLocalizable()
                );
            case $attributeAsMainMedia instanceof AssetManagerMediaLinkAsMainMedia:
                return new MediaLinkAsMainMedia(
                    $attributeAsMainMedia->isScopable(),
                    $attributeAsMainMedia->isLocalizable(),
                    $attributeAsMainMedia->getPrefix(),
                    $attributeAsMainMedia->getSuffix()
                );
            default:
                throw new \InvalidArgumentException('Unsupported attribute type as main media');
        }
    }
}
