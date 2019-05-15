<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentReferenceDataMultiSelectValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentReferenceDataMultiSelectValuesFilterSpec extends ObjectBehavior
{
    public function let(GetExistingReferenceDataCodes $getExistingReferenceDataCodes) {
        $this->beConstructedWith($getExistingReferenceDataCodes);
    }

    public function it_has_a_type()
    {
        $this->shouldHaveType(NonExistentReferenceDataMultiSelectValuesFilter::class);
    }

    public function it_filters_multi_select_values(GetExistingReferenceDataCodes $getExistingReferenceDataCodes)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::REFERENCE_DATA_MULTI_SELECT => [
                    'a_reference_data_multi_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['MiChel', 'sardou'],
                                ],
                                'tablet' => [
                                    'en_US' => ['jean', 'Claude', 'van', 'damm'],
                                    'fr_FR' => ['des', 'fRaises'],

                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'some_reference_data'
                            ]
                        ],
                    ],
                    'another_reference_data_multi_select' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'mobile' => [
                                    'en_US' => ['des', 'damm'],
                                ],
                                'tablet' => [
                                    'en_US' => ['Claude', 'fRaises'],

                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'another_reference_data'
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

        $referenceDataCodes = [
            'MiChel',
            'sardou',
            'jean',
            'Claude',
            'van',
            'damm',
            'des',
            'fRaises'
        ];

        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('some_reference_data', $referenceDataCodes)->willReturn(
            ['MiChel', 'fRaises', 'whatever']
        );

        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('another_reference_data', ['des', 'damm', 'Claude', 'fRaises'])->willReturn(
            ['Claude', 'damm']
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);

        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::REFERENCE_DATA_MULTI_SELECT => [
                    'a_reference_data_multi_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['MiChel'],
                                ],
                                'tablet' => [
                                    'en_US' => [],
                                    'fr_FR' => ['fRaises'],

                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'some_reference_data'
                            ]
                        ]
                    ],
                    'another_reference_data_multi_select' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'mobile' => [
                                    'en_US' => ['damm'],
                                ],
                                'tablet' => [
                                    'en_US' => ['Claude'],

                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'another_reference_data'
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}
