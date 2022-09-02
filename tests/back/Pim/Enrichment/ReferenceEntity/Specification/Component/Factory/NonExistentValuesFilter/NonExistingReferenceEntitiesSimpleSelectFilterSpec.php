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
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindAllExistentRecordsForReferenceEntityIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class NonExistingReferenceEntitiesSimpleSelectFilterSpec extends ObjectBehavior
{
    public function let(FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers)
    {
        $this->beConstructedWith($findAllExistentRecordsForReferenceEntityIdentifiers);
    }

    public function is_is_a_non_existent_values_filter()
    {
        $this->shouldBeAnInstanceOf(NonExistentValuesFilter::class);
    }

    public function it_filters_simple_reference_entity_links(FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                ReferenceEntityType::REFERENCE_ENTITY => [
                    'brand1' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'apple'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'Dell'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
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
            'brand' => ['apple', 'Dell']
        ];

        $findAllExistentRecordsForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes($recordCodesIndexedByReferenceEntityIdentifiers)->willReturn(
            [
                'brand' => ['apple']
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                ReferenceEntityType::REFERENCE_ENTITY => [
                    'brand1' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'apple'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ''
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
                            ]
                        ]
                    ]
                ],
            ]
        );
    }

    public function it_filters_simple_reference_entity_links_ignoring_case(FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                ReferenceEntityType::REFERENCE_ENTITY => [
                    'brand1' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'apple'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'bRaNd'
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'Dell'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'bRaNd'
                            ]
                        ],
                        [
                            'identifier' => 'product_C',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'HP'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
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
            'brand' => ['apple', 'Dell', 'HP']
        ];

        $findAllExistentRecordsForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes($recordCodesIndexedByReferenceEntityIdentifiers)->willReturn(
            [
                'brand' => ['apple', 'hp']
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                ReferenceEntityType::REFERENCE_ENTITY => [
                    'brand1' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'apple'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'bRaNd'
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ''
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'bRaNd'
                            ]
                        ],
                        [
                            'identifier' => 'product_C',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'HP'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
                            ]
                        ]
                    ],
                ],
            ]
        );
    }

    public function it_handles_null_values(FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                ReferenceEntityType::REFERENCE_ENTITY => [
                    'brand1' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'apple'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => null
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
            'brand' => ['apple'],
        ];

        $findAllExistentRecordsForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes($recordCodesIndexedByReferenceEntityIdentifiers)->willReturn(
            [
                'brand' => ['apple'],
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                ReferenceEntityType::REFERENCE_ENTITY => [
                    'brand1' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'apple'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => '',
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'color',
                            ],
                        ],
                    ]
                ],
            ]
        );
    }

    public function it_always_return_reference_entity_type(
        FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers
    ) {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                ReferenceEntityType::REFERENCE_ENTITY => [
                    'brand1' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'apple'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
                            ]
                        ],
                    ],
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

        $findAllExistentRecordsForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes([
            'brand' => ['apple'],
        ])->willReturn([]);

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                ReferenceEntityType::REFERENCE_ENTITY => [
                    'brand1' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => ''
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'brand'
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}
