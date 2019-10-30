<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentReferenceDataSimpleSelectValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentReferenceDataSimpleSelectValuesFilterSpec extends ObjectBehavior
{
    public function let(GetExistingReferenceDataCodes $getExistingReferenceDataCodes) {
        $this->beConstructedWith($getExistingReferenceDataCodes);
    }

    public function it_has_a_type()
    {
        $this->shouldHaveType(NonExistentReferenceDataSimpleSelectValuesFilter::class);
    }

    public function it_filters_reference_data_simple_select_values(GetExistingReferenceDataCodes $getExistingReferenceDataCodes)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT => [
                    'a_reference_data_simple_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    'en_US' => 'option_toto',
                                    'fr_FR' => 'OPTION_WITH_OTHER_CASE',
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'some_reference_data'
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'non_existent_option'
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'some_reference_data'
                            ]
                        ]
                    ],
                    'another_reference_data_simple_select' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'option_tata'
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
                            ],
                            'properties' => []
                        ],
                    ]
                ]
            ]
        );

        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes(
            'some_reference_data',
            [
                'option_toto',
                'OPTION_WITH_OTHER_CASE',
                'non_existent_option',
            ]
        )->willReturn(
            ['option_toto', 'Option_With_Other_Case']
        );

        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('another_reference_data', ['option_tata'])->willReturn(
            []
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);

        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT => [
                    'a_reference_data_simple_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    'en_US' => 'option_toto',
                                    'fr_FR' => 'Option_With_Other_Case',
                                ],
                            ],
                            'properties' => [
                                'reference_data_name' => 'some_reference_data'
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
                                'reference_data_name' => 'some_reference_data'
                            ]
                        ]
                    ],
                    'another_reference_data_simple_select' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ''
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
