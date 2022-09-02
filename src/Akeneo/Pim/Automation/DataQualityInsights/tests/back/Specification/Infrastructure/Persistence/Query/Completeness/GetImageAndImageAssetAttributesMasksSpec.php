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

use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\SqlGetAssetAttributesWithMediaInfoInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\FilterImageAndImageAssetAttributesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetProductAssetAndAttributesInfoInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use PhpSpec\ObjectBehavior;

class GetImageAndImageAssetAttributesMasksSpec extends ObjectBehavior
{
    public function let(
        GetRequiredAttributesMasks                  $getRequiredAttributesMasks,
        SqlGetAssetAttributesWithMediaInfoInterface $getAssetAttributeInfo,
        GetProductAssetAndAttributesInfoInterface   $getProductAssetAndAttributesInfo,
        FilterImageAndImageAssetAttributesInterface $filterImageAndImageAssetAttributes
    ): void
    {
        $this->beConstructedWith(
            $getRequiredAttributesMasks,
            $filterImageAndImageAssetAttributes,
            $getAssetAttributeInfo,
            $getProductAssetAndAttributesInfo
        );
    }

    public function it_returns_required_mask_filtered(
        $getRequiredAttributesMasks,
        $filterImageAndImageAssetAttributes,
    ): void
    {
        $familyCodes = [0 => 'family_product'];
        $masks = [
            new RequiredAttributesMask($familyCodes[0], [
                new RequiredAttributesMaskForChannelAndLocale(
                    'ecommerce',
                    'en_US',
                    [
                        "asset_code_pdf-<all_channels>-<all_locales>",
                        "asset_code_image-<all_channels>-<all_locales>",
                        "picture-<all_channels>-<all_locales>"
                    ],
                ),
            ]),
        ];
        $getRequiredAttributesMasks->fromFamilyCodes($familyCodes)->willReturn($masks);

        $expectedResult[$familyCodes[0]] = new RequiredAttributesMask(
            $familyCodes[0],
            [
                new RequiredAttributesMaskForChannelAndLocale(
                    'ecommerce',
                    'en_US',
                    [
                        "asset_code_image-<all_channels>-<all_locales>",
                        "picture-<all_channels>-<all_locales>"
                    ]
                )
            ]
        );

        $filterImageAndImageAssetAttributes->filter($familyCodes, $masks)->willReturn($expectedResult);

        $getRequiredAttributesMasks->fromFamilyCodes($familyCodes)->shouldBeCalled();
        $this->fromFamilyCodes($familyCodes)->shouldBeLike($expectedResult);
    }

    public function it_returns_empty_result_when_unknown_family_codes(
        $getRequiredAttributesMasks,
        $filterImageAndImageAssetAttributes
    ): void
    {
        $familyCodes = [0 => 'family_product'];
        $masks = [];
        $getRequiredAttributesMasks->fromFamilyCodes($familyCodes)->willReturn($masks);
        $filterImageAndImageAssetAttributes->filter($familyCodes, $masks)->willReturn([]);

        $this->fromFamilyCodes($familyCodes)->shouldBeLike([]);
    }

    public function it_returns_mask_not_filtered_when_no_asset_collection_attributes_in_family_codes(
        $getRequiredAttributesMasks,
        $filterImageAndImageAssetAttributes
    ): void
    {
        $familyCodes = [0 => 'family_product'];
        $masks = [
            new RequiredAttributesMask($familyCodes[0], [
                new RequiredAttributesMaskForChannelAndLocale(
                    'ecommerce',
                    'en_US',
                    [
                        "picture-<all_channels>-<all_locales>"
                    ],
                ),
            ]),
        ];
        $getRequiredAttributesMasks->fromFamilyCodes($familyCodes)->willReturn($masks);
        $filterImageAndImageAssetAttributes->filter($familyCodes, $masks)->willReturn($masks);

        $this->fromFamilyCodes($familyCodes)->shouldBeLike($masks);
    }
}
