<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentChannelLocaleValuesFilterSpec extends ObjectBehavior
{
    public function let(ChannelExistsWithLocaleInterface $channelsLocales)
    {
        $this->beConstructedWith($channelsLocales);
    }

    public function it_filters_values_of_non_existing_channels($channelsLocales)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType([
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'a_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'option_A'
                            ],
                        ]
                    ],
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => 'option_B'
                            ],
                            'foo' => [
                                'en_US' => 'option_B'
                            ],
                        ]
                    ]
                ],
                'another_select' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            'foo' => [
                                'en_US' => 'option_B'
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
                            'foo' => [
                                '<all_locales>' => 'plop'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $channelsLocales->doesChannelExist('ecommerce')->willReturn(true);
        $channelsLocales->doesChannelExist('foo')->willReturn(false);
        $channelsLocales->isLocaleBoundToChannel('en_US', 'ecommerce')->willReturn(true);

        $filteredRawValues = $this->filter($ongoingFilteredRawValues)->filteredRawValuesCollectionIndexedByType();
        $filteredRawValues->shouldBeLike([
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'a_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'option_A'
                            ],
                        ],
                    ],
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => 'option_B'
                            ],
                        ],
                    ],
                ],
                'another_select' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [],
                    ],
                ],
            ],
            AttributeTypes::TEXTAREA => [
                'a_description' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [],
                    ],
                ],
            ],
        ]);
    }

    public function it_filters_values_of_not_activated_locales($channelsLocales)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType([
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'a_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => 'option_A',
                                'en_CA' => 'option_A',
                                'fr_FR' => 'option_A',
                            ],
                        ],
                    ],
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            'ecommerce' => [
                                'en_CA' => 'option_B'
                            ],
                        ],
                    ],
                ],
                'another_select' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            'ecommerce' => [
                                '<all_locales>' => 'option_B'
                            ],
                        ],
                    ],
                ],
            ],
            AttributeTypes::TEXTAREA => [
                'a_description' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            '<all_channels>' => [
                                'en_US' => 'plop',
                                'fr_FR' => 'hop',
                                'en_CA' => 'bar'
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $channelsLocales->doesChannelExist('ecommerce')->willReturn(true);
        $channelsLocales->isLocaleBoundToChannel('en_US', 'ecommerce')->willReturn(true);
        $channelsLocales->isLocaleBoundToChannel('en_CA', 'ecommerce')->willReturn(false);
        $channelsLocales->isLocaleBoundToChannel('fr_FR', 'ecommerce')->willReturn(false);
        $channelsLocales->isLocaleActive('en_US')->willReturn(true);
        $channelsLocales->isLocaleActive('en_CA')->willReturn(false);
        $channelsLocales->isLocaleActive('fr_FR')->willReturn(true);

        $filteredRawValues = $this->filter($ongoingFilteredRawValues)->filteredRawValuesCollectionIndexedByType();
        $filteredRawValues->shouldBeLike([
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'a_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => 'option_A',
                            ],
                        ]
                    ],
                    [
                        'identifier' => 'product_B',
                        'values' => [],
                    ],
                ],
                'another_select' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            'ecommerce' => [
                                '<all_locales>' => 'option_B'
                            ],
                        ],
                    ],
                ],
            ],
            AttributeTypes::TEXTAREA => [
                'a_description' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            '<all_channels>' => [
                                'en_US' => 'plop',
                                'fr_FR' => 'hop',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
