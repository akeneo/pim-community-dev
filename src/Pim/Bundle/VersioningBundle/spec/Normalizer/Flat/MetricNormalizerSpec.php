<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\MetricInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization_of_product_metric()
    {
        $this->supportsNormalization([], 'flat')->shouldBe(true);
        $this->supportsNormalization([], 'csv')->shouldBe(false);
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_metric_in_many_fields_by_default()
    {
        $standardMetric = [
            'a_temperature' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'amount' => '-176.5',
                        'unit'   => 'CELSIUS',
                    ],
                ],
            ],
        ];

        $this->normalize($standardMetric, 'flat', [])->shouldReturn(
            [
                'a_temperature'      => '-176.5',
                'a_temperature-unit' => 'CELSIUS',
            ]
        );
    }

    function it_normalizes_empty_metric_in_many_fields()
    {
        $standardMetric = [
            'a_temperature' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'amount' => '',
                        'unit'   => '',
                    ],
                ],
            ],
        ];

        $this->normalize($standardMetric, 'flat', [])->shouldReturn(
            [
                'a_temperature'      => '',
                'a_temperature-unit' => '',
            ]
        );
    }

    function it_normalizes_metric_in_one_field()
    {
        $standardMetric = [
            'a_weight' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'amount' => '72.1000',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
            ],
        ];

        $this->normalize($standardMetric, 'flat', ['metric_format' => 'single_field'])->shouldReturn(
            [
                'a_weight' => '72.1000 KILOGRAM',
            ]
        );
    }

    function it_normalizes_empty_metric_with_a_single_field()
    {
        $standardMetric = [
            'a_temperature' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'amount' => '',
                        'unit'   => '',
                    ],
                ],
            ],
        ];

        $this->normalize($standardMetric, 'flat', ['metric_format' => 'single_field'])->shouldReturn(
            [
                'a_temperature' => '',
            ]
        );
    }

    function it_normalizes_localizable_metric_with_a_single_field()
    {
        $standardMetric = [
            'a_weight' => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => [
                        'amount' => '11.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => [
                        'amount' => '12.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
            ],
        ];

        $this->normalize($standardMetric, 'flat', ['metric_format' => 'single_field'])->shouldReturn(
            [
                'a_weight-fr_FR' => '11.00 KILOGRAM',
                'a_weight-en_US' => '12.00 KILOGRAM',
            ]
        );
    }

    function it_normalizes_scopable_metric_with_multiple_fields()
    {
        $standardMetric = [
            'a_weight' => [
                [
                    'locale' => null,
                    'scope'  => 'mobile',
                    'data'   => [
                        'amount' => '11.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
                [
                    'locale' => null,
                    'scope'  => 'ecommerce',
                    'data'   => [
                        'amount' => '12.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
            ],
        ];

        $this->normalize($standardMetric, 'flat', [])->shouldReturn(
            [
                'a_weight-mobile'         => '11.00',
                'a_weight-unit-mobile'    => 'KILOGRAM',
                'a_weight-ecommerce'      => '12.00',
                'a_weight-unit-ecommerce' => 'KILOGRAM',
            ]
        );
    }

    function it_normalizes_scopable_and_localizable_metric_with_a_single_field()
    {
        $standardMetric = [
            'a_weight' => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'mobile',
                    'data'   => [
                        'amount' => '11.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => [
                        'amount' => '12.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
            ],
        ];

        $this->normalize($standardMetric, 'flat', ['metric_format' => 'single_field'])->shouldReturn(
            [
                'a_weight-fr_FR-mobile'    => '11.00 KILOGRAM',
                'a_weight-en_US-ecommerce' => '12.00 KILOGRAM',
            ]
        );
    }

    function it_normalizes_scopable_and_localizable_metric_with_multiple_fields()
    {
        $standardMetric = [
            'a_weight' => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'mobile',
                    'data'   => [
                        'amount' => '11.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => [
                        'amount' => '12.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
            ],
        ];

        $this->normalize($standardMetric, 'flat', [])->shouldReturn(
            [
                'a_weight-fr_FR-mobile'         => '11.00',
                'a_weight-unit-fr_FR-mobile'    => 'KILOGRAM',
                'a_weight-en_US-ecommerce'      => '12.00',
                'a_weight-unit-en_US-ecommerce' => 'KILOGRAM',
            ]
        );
    }

    function it_throws_exception_when_the_context_metric_format_is_not_valid()
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'Value "foo" of "metric_format" context value is not allowed '.
                    '(allowed values: "single_field, multiple_fields"'
                )
            )
            ->duringNormalize([], 'flat', ['metric_format' => 'foo']);
    }
}
