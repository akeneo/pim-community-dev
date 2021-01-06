<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Factory\NonExistentValuesFilter;


use Akeneo\Pim\Enrichment\AssetManager\Component\Query\FindAllExistentAssetsForAssetFamilyIdentifiers;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

final class NonExistingAssetFamiliesMultiSelectFilterSpec extends ObjectBehavior
{
    public function let(FindAllExistentAssetsForAssetFamilyIdentifiers $allExistentAssetsForAssetFamilyIdentifiers)
    {
        $this->beConstructedWith($allExistentAssetsForAssetFamilyIdentifiers);
    }

    public function it_is_a_non_existent_values_filter()
    {
        $this->shouldBeAnInstanceOf(NonExistentValuesFilter::class);
    }

    public function it_filters_multiple_assets(FindAllExistentAssetsForAssetFamilyIdentifiers $allExistentAssetsForAssetFamilyIdentifiers)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AssetCollectionType::ASSET_COLLECTION=> [
                    'assetcollection' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['absorb_Packshot_1', 'Absorb_packshot_2', 'non_Existing_Asset'],
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'packshot'
                            ]
                        ]
                    ]
                ],
                AttributeTypes::TEXTAREA => [
                    'a_description' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'plop'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $assetCodesIndexedByAssetFamilyIdentifiers = [
            'packshot' => [
                'absorb_Packshot_1',
                'Absorb_packshot_2',
                'non_Existing_Asset'
            ]
        ];

        $allExistentAssetsForAssetFamilyIdentifiers->forAssetFamilyIdentifiersAndAssetCodes($assetCodesIndexedByAssetFamilyIdentifiers)->willReturn(
            [
                'packshot' => ['absorb_packshot_1', 'absorb_packshot_2']
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AssetCollectionType::ASSET_COLLECTION=> [
                    'assetcollection' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['absorb_Packshot_1', 'Absorb_packshot_2'],
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'packshot'
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}
