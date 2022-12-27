<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentChannelLocaleValuesFilterSpec extends ObjectBehavior
{
    public function let(
        ChannelExistsWithLocaleInterface $channelsLocales,
        GetAttributes $getAttributes
    )
    {
        $this->beConstructedWith($channelsLocales, $getAttributes);
    }

    public function it_filters_values_of_non_existing_channels(
        $channelsLocales,
        $getAttributes
    ) {
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
        $channelsLocales->getLocaleNameWithRightCase('en_US')->willReturn('en_US');

        $attributes = $this->getAttributes();
        $getAttributes->forCode('a_select')->willReturn($attributes['a_select']);
        $getAttributes->forCode('another_select')->willReturn($attributes['another_select']);
        $getAttributes->forCode('a_description')->willReturn($attributes['a_description']);

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

    public function it_filters_values_of_not_activated_locales($channelsLocales, GetAttributes $getAttributes)
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
        $channelsLocales->getLocaleNameWithRightCase('en_CA')->willReturn('en_CA');
        $channelsLocales->getLocaleNameWithRightCase('fr_FR')->willReturn('fr_FR');
        $channelsLocales->getLocaleNameWithRightCase('en_US')->willReturn('en_US');

        $attributes = $this->getAttributes();
        $getAttributes->forCode('a_select')->willReturn($attributes['a_select']);
        $getAttributes->forCode('another_select')->willReturn($attributes['another_select']);
        $getAttributes->forCode('a_description')->willReturn($attributes['a_description']);

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

    public function it_filters_values_with_case_insensitive_locales($channelsLocales, GetAttributes $getAttributes)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType([
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'a_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            'ecommerce' => [
                                'fr_fr' => 'option_A',
                                'en_US' => 'option_A',
                                'iu_cans_ca' => 'option_C',
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
        ]);

        $channelsLocales->doesChannelExist('ecommerce')->willReturn(true);
        $channelsLocales->isLocaleBoundToChannel('en_US', 'ecommerce')->willReturn(true);
        $channelsLocales->isLocaleBoundToChannel('fr_fr', 'ecommerce')->willReturn(true);
        $channelsLocales->isLocaleBoundToChannel('iu_cans_ca', 'ecommerce')->willReturn(true);
        $channelsLocales->isLocaleActive('en_US')->willReturn(true);
        $channelsLocales->isLocaleActive('fr_FR')->willReturn(true);
        $channelsLocales->isLocaleActive('iu_Cans_CA')->willReturn(true);
        $channelsLocales->getLocaleNameWithRightCase('fr_fr')->willReturn('fr_FR');
        $channelsLocales->getLocaleNameWithRightCase('en_US')->willReturn('en_US');
        $channelsLocales->getLocaleNameWithRightCase('iu_cans_ca')->willReturn('iu_Cans_CA');

        $attributes = $this->getAttributes();
        $getAttributes->forCode('a_select')->willReturn($attributes['a_select']);
        $getAttributes->forCode('another_select')->willReturn($attributes['another_select']);

        $filteredRawValues = $this->filter($ongoingFilteredRawValues)->filteredRawValuesCollectionIndexedByType();
        $filteredRawValues->shouldBeLike([
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'a_select' => [
                    [
                        'identifier' => 'product_A',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => 'option_A',
                                'fr_FR' => 'option_A',
                                'iu_Cans_CA' => 'option_C',
                            ],
                        ]
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
        ]);
    }

    private function getAttributes(): array {
        return [
            'a_select' => new Attribute(
                'a_select',
                AttributeTypes::OPTION_SIMPLE_SELECT,
                [],
                false,
                false,
                null,
                null,
                null,
                AttributeTypes::BACKEND_TYPE_OPTION,
                []
            ),
            'another_select' => new Attribute(
                'another_select',
                AttributeTypes::OPTION_SIMPLE_SELECT,
                [],
                false,
                false,
                null,
                null,
                null,
                AttributeTypes::BACKEND_TYPE_OPTION,
                []
            ),
            'a_description' => new Attribute(
                'a_description',
                AttributeTypes::TEXTAREA,
                [],
                false,
                false,
                null,
                null,
                null,
                AttributeTypes::BACKEND_TYPE_TEXTAREA,
                []
            ),
            'a_locale_specific_select' => new Attribute(
                'a_locale_specific_select',
                AttributeTypes::OPTION_SIMPLE_SELECT,
                [],
                true,
                false,
                null,
                null,
                null,
                AttributeTypes::BACKEND_TYPE_OPTION,
                ['fr_FR']
            ),
        ];
    }
}
