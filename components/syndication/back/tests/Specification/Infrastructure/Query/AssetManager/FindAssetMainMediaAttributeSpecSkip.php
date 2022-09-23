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

namespace Specification\Akeneo\Platform\Syndication\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\AttributeAsMainMedia;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAttributeAsMainMediaInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\MediaFileAsMainMedia as AssetManagerMediaFileAsMainMedia;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\MediaLinkAsMainMedia as AssetManagerMediaLinkAsMainMedia;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\MediaFileAsMainMedia;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\MediaLinkAsMainMedia;
use PhpSpec\ObjectBehavior;

/**
 * @require Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAttributeAsMainMediaInterface
 */

class FindAssetMainMediaAttributeSpec extends ObjectBehavior
{
    public function let(GetAttributeAsMainMediaInterface $getAttributeAsMainMedia): void
    {
        $this->beConstructedWith($getAttributeAsMainMedia);
    }

    public function it_returns_an_media_link_attribute_when_asset_as_main_media_is_media_link(
        GetAttributeAsMainMediaInterface $getAttributeAsMainMedia
    ): void {
        $getAttributeAsMainMedia->forAssetFamilyCode('asset_family_code')->willReturn(
            new AssetManagerMediaLinkAsMainMedia(false, false, 'https://', '.png')
        );

        $this->forAssetFamily('asset_family_code')->shouldBeLike(
            new MediaLinkAsMainMedia(false, false, 'https://', '.png')
        );
    }

    public function it_returns_an_media_file_attribute_when_asset_as_main_media_is_media_file(
        GetAttributeAsMainMediaInterface $getAttributeAsMainMedia
    ): void {
        $getAttributeAsMainMedia->forAssetFamilyCode('asset_family_code')->willReturn(
            new AssetManagerMediaFileAsMainMedia(false, false)
        );

        $this->forAssetFamily('asset_family_code')->shouldBeLike(new MediaFileAsMainMedia(false, false));
    }

    public function it_throws_an_error_when_type_is_unsupported(
        GetAttributeAsMainMediaInterface $getAttributeAsMainMedia,
        AttributeAsMainMedia $attributeAsMainMedia
    ): void {
        $getAttributeAsMainMedia->forAssetFamilyCode('asset_family_code')->willReturn($attributeAsMainMedia);

        $this
            ->shouldThrow(new \InvalidArgumentException('Unsupported attribute type as main media'))
            ->during('forAssetFamily', ['asset_family_code']);
    }
}
