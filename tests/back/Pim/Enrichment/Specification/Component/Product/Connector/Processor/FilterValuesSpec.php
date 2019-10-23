<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\FilterValues;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FilterValuesSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create');
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(FilterValues::class);
    }

    function it_filters_localizable_values()
    {
        $this
            ->filterByLocaleCodes(['en_US'])
            ->execute(
                [
                    'attribute_code_1' => [
                        [
                            'locale' => 'fr_FR',
                            'scope' => null,
                            'data' => 'foo'
                        ],
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => 'foo'
                        ]
                    ],
                    'attribute_code_2' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'foo'
                        ]
                    ]
                ]
            )->shouldBeLike(
                [
                    'attribute_code_1' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => 'foo'
                        ]
                    ],
                    'attribute_code_2' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'foo'
                        ]
                    ]
                ]
            );
    }

    function it_filters_scopable_values()
    {
        $this
            ->filterByChannelCode('ecommerce')
            ->execute(
                [
                    'attribute_code_1' => [
                        [
                            'locale' => null,
                            'scope' => 'ecommerce',
                            'data' => 'foo'
                        ],
                        [
                            'locale' => null,
                            'scope' => 'tablet',
                            'data' => 'foo'
                        ]
                    ],
                    'attribute_code_2' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'foo'
                        ]
                    ]
                ]
            )->shouldBeLike(
                [
                    'attribute_code_1' => [
                        [
                            'locale' => null,
                            'scope' => 'ecommerce',
                            'data' => 'foo'
                        ]
                    ],
                    'attribute_code_2' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'foo'
                        ]
                    ]
                ]
            );
    }

    function it_filters_the_values_by_attribute_code()
    {
        $this
            ->filterByAttributeCodes(['attribute_code_1'])
            ->execute(
                [
                    'attribute_code_1' => [
                        [
                            'locale' => null,
                            'scope' => 'ecommerce',
                            'data' => 'foo'
                        ],
                        [
                            'locale' => null,
                            'scope' => 'tablet',
                            'data' => 'foo'
                        ]
                    ],
                    'attribute_code_2' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'foo'
                        ]
                    ]
                ]
            )->shouldBeLike(
                [
                    'attribute_code_1' => [
                        [
                            'locale' => null,
                            'scope' => 'ecommerce',
                            'data' => 'foo'
                        ],
                        [
                            'locale' => null,
                            'scope' => 'tablet',
                            'data' => 'foo'
                        ]
                    ]
                ]
            );
    }

    function it_filters_all_values_for_an_attribute()
    {
        $this
            ->filterByLocaleCodes(['en_US', 'fr_FR'])
            ->execute(
                [
                    'attribute_code_1' => [
                        [
                            'locale' => 'de_DE',
                            'scope' => null,
                            'data' => 'foo'
                        ],
                        [
                            'locale' => 'es_ES',
                            'scope' => null,
                            'data' => 'foo'
                        ]
                    ]
                ]
            )->shouldBeLike([]);
    }
}
