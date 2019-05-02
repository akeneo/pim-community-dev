<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner\OnGoingCleanedRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OnGoingCleanedRawValuesSpec extends ObjectBehavior
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
        ];

        $this->beConstructedThrough('fromNonCleanedValuesCollectionIndexedByType', [$rawValues]);
    }

    public function it_has_a_type()
    {
        $this->shouldHaveType(OnGoingCleanedRawValues::class);
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
                    ]
                ]
            ],
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
            ],
        ];

        $this->nonCleanedValuesOfTypes(AttributeTypes::OPTION_MULTI_SELECT, AttributeTypes::OPTION_SIMPLE_SELECT)
            ->shouldReturn($values);
    }

    public function it_adds_some_cleaned_values()
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
                        ]
                    ]
                ]
            ],
            AttributeTypes::OPTION_MULTI_SELECT => []
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
                        ]
                    ]
                ]
            ]
        ];

        $expected = new OnGoingCleanedRawValues(
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
                        ]
                    ]
                ],
            ],
            $notFilteredValues);
        $this->addCleanedValuesIndexedByType($rawValues)->shouldBeLike($expected);
    }
}
