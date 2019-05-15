<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OnGoingFilteredRawValuesSpec extends ObjectBehavior
{
    public function let() {
        $rawValues = [
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'a_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'option_toto'
                            ],
                        ],
                        'properties' => [],
                    ],
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => 'option_tata'
                            ],
                        ],
                        'properties' => [],
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
                        ],
                        'properties' => [],
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
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $this->beConstructedThrough('fromNonFilteredValuesCollectionIndexedByType', [$rawValues]);
    }

    public function it_has_a_type()
    {
        $this->shouldHaveType(OnGoingFilteredRawValues::class);
    }

    public function it_returns_the_values_of_a_given_type()
    {
        $values = [
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
                    ],
                    'properties' => [],
                ]
            ],
            'a_select' => [
                [
                    'identifier' => 'product_A',
                    'values' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'option_toto'
                        ],
                    ],
                    'properties' => [],
                ],
                [
                    'identifier' => 'product_B',
                    'values' => [
                        'ecommerce' => [
                            'en_US' => 'option_tata'
                        ],
                    ],
                    'properties' => [],
                ]
            ],
        ];

        $this->notFilteredValuesOfTypes(AttributeTypes::OPTION_MULTI_SELECT, AttributeTypes::OPTION_SIMPLE_SELECT)
            ->shouldReturn($values);
    }

    public function it_adds_some_filtered_values()
    {
        $rawValues = [
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'a_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'option_toto'
                            ],
                        ],
                        'properties' => [],
                    ],
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => ''
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ],
            AttributeTypes::OPTION_MULTI_SELECT => [
                'a_multi_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => ['sardou'],
                            ],
                            'tablet' => [
                                'en_US' => ['jean', 'van', 'damm'],
                                'fr_FR' => ['des'],

                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $notFilteredValues = [
            AttributeTypes::TEXTAREA => [
                'a_description' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'plop'
                            ]
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        /** @var OnGoingFilteredRawValues $newOngoingFilteredRawValues */
        $newOngoingFilteredRawValues = $this->addFilteredValuesIndexedByType($rawValues);
        $newOngoingFilteredRawValues->nonFilteredRawValuesCollectionIndexedByType()->shouldBeLike($notFilteredValues);
    }
}
