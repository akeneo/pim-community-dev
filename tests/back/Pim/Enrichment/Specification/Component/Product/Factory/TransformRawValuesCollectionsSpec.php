<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\TransformRawValuesCollections;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

class TransformRawValuesCollectionsSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TransformRawValuesCollections::class);
    }

    function it_transforms_raw_values_collections_to_a_values_collections_indexed_by_type(GetAttributes $getAttributes)
    {
        $getAttributes->forCodes(['number', 'number2', '123', 'number3'])->willReturn(
            [
                'number' => new Attribute('number', AttributeTypes::NUMBER, [], false, false, null, false, 'decimal', []),
                'number2' => new Attribute('number2', AttributeTypes::NUMBER, [], false, false, null, false, 'decimal', []),
                'number3' => new Attribute('number3', AttributeTypes::NUMBER, [], false, false, null, false, 'decimal', []),
                '123' => new Attribute('123', AttributeTypes::NUMBER, [], false, false, null, false, 'decimal', []),
            ]
        );
        $this->toValueCollectionsIndexedByType([
            'productA' => [
                'number' => [
                    '<all_channels>' => [
                        '<all_locales>' => 5
                    ]
                ],
                'number2' => [
                    '<all_channels>' => [
                        '<all_locales>' => 6
                    ]
                ],
                '123' => [
                    '<all_channels>' => [
                        '<all_locales>' => 6
                    ]
                ]
            ],
            'productB' => [
                'number' => [
                    '<all_channels>' => [
                        '<all_locales>' => 7
                    ]
                ],
                'number3' => [
                    '<all_channels>' => [
                        '<all_locales>' => 8
                    ]
                ],
            ]
        ])->shouldReturn([
            AttributeTypes::NUMBER => [
                'number' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 5
                            ]
                        ],
                        'properties' => [],
                    ],
                    [
                        'identifier' => 'productB',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 7
                            ]
                        ],
                        'properties' => [],
                    ]
                ],
                'number2' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 6
                            ]
                        ],
                        'properties' => [],
                    ]
                ],
                '123' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 6
                            ]
                        ],
                        'properties' => [],
                    ]
                ],
                'number3' => [
                    [
                        'identifier' => 'productB',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 8
                            ]
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ]);
    }
}
