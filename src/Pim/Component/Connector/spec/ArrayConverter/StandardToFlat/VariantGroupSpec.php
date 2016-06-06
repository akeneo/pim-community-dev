<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

class VariantGroupSpec extends ObjectBehavior
{
    function let(ProductValueConverter $valueConverter)
    {
        $this->beConstructedWith($valueConverter);
    }

    function it_converts_from_standard_to_flat_format($valueConverter)
    {
        $valueConverter->convertField('blade_length', [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => [
                    'data' => '80',
                    'unit' => 'CENTIMETER'
                ]
            ]
        ])->willReturn([
            'blade_length'      => '80',
            'blade_length-unit' => 'CENTIMETER',
        ]);

        $valueConverter->convertField('blade_material', [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => ['wood', 'leather']
            ]
        ])->willReturn(['blade_material' => 'wood,leather']);

        $valueConverter->convertField('description', [
            [
                'locale' => 'fr_FR',
                'scope'  => 'ecommerce',
                'data'   => '<p>description</p>',
            ],
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => '<p>description</p>',
            ]
        ])->willReturn([
            'description-fr_FR-ecommerce' => '<p>description</p>',
            'description-en_US-ecommerce' => '<p>description</p>',
        ]);

        $expected = [
            'code'                        => 'swords',
            'label-en_US'                 => 'Swords',
            'label-fr_FR'                 => 'Épées',
            'axis'                        => 'blade_length,color',
            'type'                        => 'VARIANT',
            'blade_length'                => '80',
            'blade_length-unit'           => 'CENTIMETER',
            'blade_material'              => 'wood,leather',
            'description-fr_FR-ecommerce' => '<p>description</p>',
            'description-en_US-ecommerce' => '<p>description</p>',
        ];

        $item = [
            'code'   => 'swords',
            'labels' => [
                'en_US' => 'Swords',
                'fr_FR' => 'Épées'
            ],
            'axis'   => ['blade_length', 'color'],
            'type'   => 'VARIANT',
            'values' => [
                'blade_length'   => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            'data' => '80',
                            'unit' => 'CENTIMETER'
                        ]
                    ]
                ],
                'blade_material' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['wood', 'leather']
                    ]
                ],
                'description'    => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>',
                    ]
                ]
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
