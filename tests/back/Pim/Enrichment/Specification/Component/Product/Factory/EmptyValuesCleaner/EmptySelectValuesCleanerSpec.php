<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner\EmptySelectValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner\OnGoingCleanedRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EmptySelectValuesCleanerSpec extends ObjectBehavior
{
    public function it_has_a_type()
    {
        $this->shouldHaveType(EmptySelectValuesCleaner::class);
    }

    public function it_cleans_select_values()
    {
        $original = OnGoingCleanedRawValues::fromNonCleanedValuesCollectionIndexedByType(
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
                                    'en_US' => [],
                                ],
                                'tablet' => [
                                    'en_US' => [],
                                    'fr_FR' => [],

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
            [
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

        $this->clean($original)->shouldBeLike($expected);
    }
}
