<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\FilterImageAndImageAssetAttributesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\GetAttributeTypesProductModelMasksQuery;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use PhpSpec\ObjectBehavior;

class GetAttributeTypesProductModelMasksWithAssetsQuerySpec extends ObjectBehavior
{
    public function let(
        GetAttributeTypesProductModelMasksQuery     $getAttributeTypesProductModelMasksQuery,
        FilterImageAndImageAssetAttributesInterface $filterImageAndImageAssetAttributes
    ): void
    {
        $this->beConstructedWith($getAttributeTypesProductModelMasksQuery, $filterImageAndImageAssetAttributes);
    }

    public function it_excludes_asset_collections_with_no_images_as_main_media(
        $getAttributeTypesProductModelMasksQuery,
        $filterImageAndImageAssetAttributes
    ): void
    {
        $productModelId = new ProductModelId(1);
        $familyCodes = [0 => 'headphones'];

        $mask =
            new RequiredAttributesMask($familyCodes[0], [
                new RequiredAttributesMaskForChannelAndLocale(
                    'ecommerce',
                    'en_US',
                    [
                        "asset_pdf-<all_channels>-<all_locales>",
                        "asset_image-<all_channels>-<all_locales>",
                        "picture-<all_channels>-<all_locales>"
                    ],
                ),
            ]);

        $getAttributeTypesProductModelMasksQuery->execute($productModelId)->willReturn($mask);

        $filteredResult[$familyCodes[0]] = new RequiredAttributesMask(
            $familyCodes[0],
            [
                new RequiredAttributesMaskForChannelAndLocale(
                    'ecommerce',
                    'en_US',
                    [
                        "asset_image-<all_channels>-<all_locales>",
                        "picture-<all_channels>-<all_locales>"
                    ]
                )
            ]
        );
        $filterImageAndImageAssetAttributes->filter($familyCodes, [$mask])->willReturn($filteredResult);

        $this->execute($productModelId)->shouldBeLike($filteredResult[$familyCodes[0]]);
    }

    public function it_will_return_null_if_filter_image_is_empty(
        $getAttributeTypesProductModelMasksQuery,
        $filterImageAndImageAssetAttributes
    ): void
    {
        $productModelId = new ProductModelId(1);
        $familyCodes = [0 => 'headphones'];
        $mask =
            new RequiredAttributesMask(
                $familyCodes[0],
                [new RequiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US', [])]
            );
        $getAttributeTypesProductModelMasksQuery->execute($productModelId)->willReturn($mask);

        $filterImageAndImageAssetAttributes->filter($familyCodes, [$mask])->willReturn([]);

        $this->execute($productModelId)->shouldBeLike(null);
    }

    public function it_returns_null_if_no_mask(
        $getAttributeTypesProductModelMasksQuery,
        $filterImageAndImageAssetAttributes
    ): void
    {
        $productModelId = new ProductModelId(1);
        $getAttributeTypesProductModelMasksQuery->execute($productModelId)->willReturn(null);

        $filterImageAndImageAssetAttributes->filter([], [])->shouldNotBeCalled();

        $this->execute($productModelId)->shouldBeLike(null);
    }
}
