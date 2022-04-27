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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetProductAssetAndAttributesInfoInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use PhpSpec\ObjectBehavior;

class FilterImageAndImageAssetAttributesSpec extends ObjectBehavior
{
    public function let(
        SqlGetAssetAttributesWithMediaInfoInterface $getAssetAttributeInfo,
        GetProductAssetAndAttributesInfoInterface   $getProductAssetAndAttributesInfo
    ): void
    {
        $this->beConstructedWith($getAssetAttributeInfo, $getProductAssetAndAttributesInfo);
    }

    public function it_filters_masks_with_family_codes_and_media_file_type($getProductAssetAndAttributesInfo, $getAssetAttributeInfo): void
    {
        $productFamilyCode = 'family_product';
        $familyCodes = [0 => $productFamilyCode];
        $assetFamilyIdentifiers = [
            'media_file_image' => 'assetFamilyIdentifierImage',
            'media_file_pdf' => 'assetFamilyIdentifierPdf',
        ];

        $productAssetAndAttributesInfo = [
            $productFamilyCode => [
                [
                    'attribute_code' => 'asset_code_image',
                    'asset_family_identifier' => $assetFamilyIdentifiers['media_file_image']
                ],
                [
                    'attribute_code' => 'asset_code_pdf',
                    'asset_family_identifier' => $assetFamilyIdentifiers['media_file_pdf']
                ]
            ]
        ];
        $getProductAssetAndAttributesInfo->forProductFamilyCodes($familyCodes)->willReturn($productAssetAndAttributesInfo);

        $assetAttributesInfo = [
            [
                'identifier' => $assetFamilyIdentifiers['media_file_image'],
                'attribute_as_main_media' => 'media_' . $assetFamilyIdentifiers['media_file_image'] . '_fingerprint',
                'attribute_type' => 'media_file',
                'media_type' => 'image',
            ],
            [
                'identifier' => $assetFamilyIdentifiers['media_file_pdf'],
                'attribute_as_main_media' => 'media_' . $assetFamilyIdentifiers['media_file_pdf'] . '_fingerprint',
                'attribute_type' => 'media_file',
                'media_type' => 'pdf',
            ],
        ];
        $getAssetAttributeInfo
            ->forFamilyIdentifiers([$assetFamilyIdentifiers['media_file_image'], $assetFamilyIdentifiers['media_file_pdf']])
            ->willReturn($assetAttributesInfo);

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
        $this->filter($familyCodes, $masks)->shouldBeLike($expectedResult);
    }

    public function it_filters_masks_with_family_codes_and_media_link_type($getProductAssetAndAttributesInfo, $getAssetAttributeInfo): void
    {
        $productFamilyCode = 'family_product';
        $familyCodes = [0 => $productFamilyCode];
        $assetFamilyIdentifiers = [
            'media_link_image' => 'assetFamilyIdentifierImage',
            'media_link_pdf' => 'assetFamilyIdentifierPdf',
        ];

        $productAssetAndAttributesInfo = [
            $productFamilyCode => [
                [
                    'attribute_code' => 'asset_code_image',
                    'asset_family_identifier' => $assetFamilyIdentifiers['media_link_image']
                ],
                [
                    'attribute_code' => 'asset_code_pdf',
                    'asset_family_identifier' => $assetFamilyIdentifiers['media_link_pdf']
                ]
            ]
        ];
        $getProductAssetAndAttributesInfo->forProductFamilyCodes($familyCodes)->willReturn($productAssetAndAttributesInfo);

        $assetAttributesInfo = [
            [
                'identifier' => $assetFamilyIdentifiers['media_link_image'],
                'attribute_as_main_media' => 'media_' . $assetFamilyIdentifiers['media_link_image'] . '_fingerprint',
                'attribute_type' => 'media_link',
                'media_type' => 'image',
            ],
            [
                'identifier' => $assetFamilyIdentifiers['media_link_pdf'],
                'attribute_as_main_media' => 'media_' . $assetFamilyIdentifiers['media_link_pdf'] . '_fingerprint',
                'attribute_type' => 'media_link',
                'media_type' => 'other',
            ],
        ];
        $getAssetAttributeInfo
            ->forFamilyIdentifiers([$assetFamilyIdentifiers['media_link_image'], $assetFamilyIdentifiers['media_link_pdf']])
            ->willReturn($assetAttributesInfo);

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
        $this->filter($familyCodes, $masks)->shouldBeLike($expectedResult);
    }
}
