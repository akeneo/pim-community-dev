<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentSelectValuesFilter;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NonExistentSelectValuesFilterSpec extends ObjectBehavior
{
    public function let(GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes) {
        $this->beConstructedWith($getExistingAttributeOptionCodes);
    }

    public function it_has_a_type()
    {
        $this->shouldHaveType(NonExistentSelectValuesFilter::class);
    }

    public function it_filters_select_values(GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::OPTION_SIMPLE_SELECT => [
                    'a_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'option_toto'
                                ],
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'option_tata'
                                ],
                            ]
                        ]
                    ]
                ],
                AttributeTypes::OPTION_MULTI_SELECT => [
                    'a_multi_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['michel', 'sardou'],
                                ],
                                'tablet' => [
                                    'en_US' => ['jean', 'claude', 'van', 'damm'],
                                    'fr_FR' => ['des', 'fraises'],

                                ],
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

        $optionCodes = [
            'a_select' => [
                'option_toto',
                'option_tata',
            ],
            'a_multi_select' => [
                'michel',
                'sardou',
                'jean',
                'claude',
                'van',
                'damm',
                'des',
                'fraises'
            ]
        ];

        $getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode($optionCodes)->willReturn(
            [
                'a_select' => ['option_toto'],
                'a_multi_select' => ['michel', 'fraises']
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::OPTION_SIMPLE_SELECT => [
                    'a_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'option_toto'
                                ],
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ''
                                ],
                            ]
                        ]
                    ]
                ],
                AttributeTypes::OPTION_MULTI_SELECT => [
                    'a_multi_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['michel'],
                                ],
                                'tablet' => [
                                    'en_US' => [],
                                    'fr_FR' => [1 => 'fraises'],

                                ],
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}
