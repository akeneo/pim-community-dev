<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindAllExistentRecordsForReferenceEntityIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class NonExistingReferenceEntitiesMultiSelectFilterSpec extends ObjectBehavior
{
    public function let(FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers)
    {
        $this->beConstructedWith($findAllExistentRecordsForReferenceEntityIdentifiers);
    }

    public function is_is_a_non_existent_values_filter()
    {
        $this->shouldBeAnInstanceOf(NonExistentValuesFilter::class);
    }

    public function it_filters_multiple_reference_entity_links(FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION => [
                    'colors' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['Blue', 'Green'],
                                ],
                                'tablet' => [
                                    'en_US' => ['Red', 'Yellow', 'Purple', 'Orange'],
                                    'fr_FR' => ['Black', 'Grey'],

                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'color'
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

        $recordCodesIndexedByReferenceEntityIdentifiers = [
            'color' => [
                'Blue',
                'Green',
                'Red',
                'Yellow',
                'Purple',
                'Orange',
                'Black',
                'Grey'
            ]
        ];

        $findAllExistentRecordsForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes($recordCodesIndexedByReferenceEntityIdentifiers)->willReturn(
            [
                'color' => ['Blue', 'Grey']
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION => [
                    'colors' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['Blue'],
                                ],
                                'tablet' => [
                                    'en_US' => [],
                                    'fr_FR' => [1 => 'Grey'],

                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'color'
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}
