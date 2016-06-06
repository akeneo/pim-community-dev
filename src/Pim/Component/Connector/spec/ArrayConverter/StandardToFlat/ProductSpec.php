<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

class ProductSpec extends ObjectBehavior
{
    function let(ProductValueConverter $valueConverter)
    {
        $this->beConstructedWith($valueConverter);
    }

    function it_converts_from_standard_to_flat_format($valueConverter)
    {
        $valueConverter->convertField('sku', [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => '10699783'
            ]
        ])->willReturn(['sku' => '10699783']);

        $valueConverter->convertField('super_price', [
            [
                'locale' => 'de_DE',
                'scope'  => 'ecommerce',
                'data'   => [
                    [
                        'data'     => '10',
                        'currency' => 'EUR'
                    ],
                    [
                        'data'     => '9',
                        'currency' => 'USD'
                    ],
                ]
            ],
            [
                'locale' => 'fr_FR',
                'scope'  => 'ecommerce',
                'data'   => [
                    [
                        'data'     => '30',
                        'currency' => 'EUR'
                    ],
                    [
                        'data'     => '29',
                        'currency' => 'USD'
                    ],
                ]
            ]
        ])->willReturn([
            'super_price-de_DE-ecommerce-EUR' => '10',
            'super_price-de_DE-ecommerce-USD' => '9',
            'super_price-fr_FR-ecommerce-EUR' => '30',
            'super_price-fr_FR-ecommerce-USD' => '29',
        ]);

        $valueConverter->convertField('total_megapixels', [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => '50'
            ]
        ])->willReturn(['total_megapixels' => '50']);

        $valueConverter->convertField('tshirt_materials', [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => []
            ]
        ])->willReturn(['tshirt_materials' => '']);

        $valueConverter->convertField('tshirt_style', [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => ['vneck', 'large']
            ]
        ])->willReturn(['tshirt_style' => 'vneck,large']);

        $valueConverter->convertField('weight', [
            [
                'locale' => 'de_DE',
                'scope'  => 'print',
                'data'   => [
                    'unit' => 'MEGAHERTZ',
                    'data' => '100'
                ]
            ]
        ])->willReturn([
            'weight-de_DE-print'      => '100',
            'weight-de_DE-print-unit' => 'MEGAHERTZ',
        ]);

        $valueConverter->convertField('maximum_scan_size', [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => [
                    'unit' => 'MILLIMETER',
                    'data' => '50'
                ]
            ]
        ])->willReturn([
            'maximum_scan_size'      => '50',
            'maximum_scan_size-unit' => 'MILLIMETER',
        ]);

        $expected = [
            'sku'                             => '10699783',
            'categories'                      => 'audio_video_sales,loudspeakers,sony',
            'enabled'                         => '1',
            'family'                          => 'loudspeakers',
            'groups'                          => 'sound,audio,mp3,speakers',
            'UPSELL-groups'                   => '',
            'UPSELL-products'                 => '',
            'X_SELL-groups'                   => 'akeneo_tshirt,oro_tshirt',
            'X_SELL-products'                 => 'AKN_TS,ORO_TSH',
            'super_price-de_DE-ecommerce-EUR' => '10',
            'super_price-de_DE-ecommerce-USD' => '9',
            'super_price-fr_FR-ecommerce-EUR' => '30',
            'super_price-fr_FR-ecommerce-USD' => '29',
            'total_megapixels'                => '50',
            'tshirt_materials'                => '',
            'tshirt_style'                    => 'vneck,large',
            'weight-de_DE-print'              => '100',
            'weight-de_DE-print-unit'         => 'MEGAHERTZ',
            'maximum_scan_size'               => '50',
            'maximum_scan_size-unit'          => 'MILLIMETER',
        ];

        $item = [
            'sku'               => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '10699783'
                ]
            ],
            'categories'        => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled'           => true,
            'family'            => 'loudspeakers',
            'groups'            => ['sound', 'audio', 'mp3'],
            'variant_group'     => 'speakers',
            'associations'      => [
                'UPSELL' => [
                    'groups'   => [],
                    'products' => []
                ],
                'X_SELL' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKN_TS', 'ORO_TSH']
                ]
            ],
            'super_price'       => [
                [
                    'locale' => 'de_DE',
                    'scope'  => 'ecommerce',
                    'data'   => [
                        [
                            'data'     => '10',
                            'currency' => 'EUR'
                        ],
                        [
                            'data'     => '9',
                            'currency' => 'USD'
                        ],
                    ]
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                    'data'   => [
                        [
                            'data'     => '30',
                            'currency' => 'EUR'
                        ],
                        [
                            'data'     => '29',
                            'currency' => 'USD'
                        ],
                    ]
                ]
            ],
            'total_megapixels'  => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '50'
                ]
            ],
            'tshirt_materials'  => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => []
                ]
            ],
            'tshirt_style'      => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['vneck', 'large']
                ]
            ],
            'weight'            => [
                [
                    'locale' => 'de_DE',
                    'scope'  => 'print',
                    'data'   => [
                        'unit' => 'MEGAHERTZ',
                        'data' => '100'
                    ]
                ]
            ],
            'maximum_scan_size' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'unit' => 'MILLIMETER',
                        'data' => '50'
                    ]
                ]
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
