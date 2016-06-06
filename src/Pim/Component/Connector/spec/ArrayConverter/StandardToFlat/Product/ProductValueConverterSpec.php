<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;

class ProductValueConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver);
    }

    function it_converts_boolean_fields_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('auto_focus_lock', null, 'print')->willReturn('auto_focus_lock-print');
        $columnsResolver->resolveFlatAttributeName('iscolor', null, null)->willReturn('iscolor');

        $expected1 = ['auto_focus_lock-print' => '0'];
        $expected2 = ['iscolor' => '1'];

        // auto_focus_lock
        $data1 = [
            [
                'locale' => null,
                'scope'  => 'print',
                'data'   => false
            ]
        ];

        // iscolor
        $data2 = [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => true
            ]
        ];

        $this->convertField('auto_focus_lock', $data1)->shouldReturn($expected1);
        $this->convertField('iscolor', $data2)->shouldReturn($expected2);
    }

    function it_converts_metric_fields_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('weight', 'de_DE', 'print')->willReturn('weight-de_DE-print');
        $columnsResolver->resolveFlatAttributeName('maximum_scan_size', null, null)->willReturn('maximum_scan_size');

        $expected1 = [
            'weight-de_DE-print'      => '100',
            'weight-de_DE-print-unit' => 'MEGAHERTZ',
        ];

        $expected2 = [
            'maximum_scan_size'      => '50',
            'maximum_scan_size-unit' => 'MILLIMETER',
        ];

        // weight
        $data1 = [
            [
                'locale' => 'de_DE',
                'scope'  => 'print',
                'data'   => [
                    'unit' => 'MEGAHERTZ',
                    'data' => '100'
                ]
            ]
        ];

        // maximum_scan_size
        $data2 = [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => [
                    'unit' => 'MILLIMETER',
                    'data' => '50'
                ]
            ]
        ];

        $this->convertField('weight', $data1)->shouldReturn($expected1);
        $this->convertField('maximum_scan_size', $data2)->shouldReturn($expected2);
    }

    function it_converts_price_fields_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('super_price', 'de_DE', 'ecommerce')->willReturn('super_price-de_DE-ecommerce');
        $columnsResolver->resolveFlatAttributeName('super_price', 'fr_FR', 'ecommerce')->willReturn('super_price-fr_FR-ecommerce');

        $expected = [
            'super_price-de_DE-ecommerce-EUR' => '10',
            'super_price-de_DE-ecommerce-USD' => '9',
            'super_price-fr_FR-ecommerce-EUR' => '30',
            'super_price-fr_FR-ecommerce-USD' => '29',
        ];

        // super_price
        $data = [
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
        ];

        $this->convertField('super_price', $data)->shouldReturn($expected);
    }

    function it_converts_fields_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('sku', null, null)->willReturn('sku');
        $columnsResolver->resolveFlatAttributeName('total_megapixels', null, null)->willReturn('total_megapixels');
        $columnsResolver->resolveFlatAttributeName('tshirt_materials', null, null)->willReturn('tshirt_materials');
        $columnsResolver->resolveFlatAttributeName('tshirt_style', null, null)->willReturn('tshirt_style');

        $expected1 = ['sku' => '10699783'];
        $expected2 = ['total_megapixels' => '50'];
        $expected3 = ['tshirt_materials' => ''];
        $expected4 = ['tshirt_style' => 'vneck,large'];

        // sku
        $data1 = [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => '10699783'
            ]
        ];

        // total_megapixels
        $data2 = [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => '50'
            ]
        ];

        // tshirt_materials
        $data3 = [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => []
            ]
        ];

        // tshirt_style
        $data4 = [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => ['vneck', 'large']
            ]
        ];

        $this->convertField('sku', $data1)->shouldReturn($expected1);
        $this->convertField('total_megapixels', $data2)->shouldReturn($expected2);
        $this->convertField('tshirt_materials', $data3)->shouldReturn($expected3);
        $this->convertField('tshirt_style', $data4)->shouldReturn($expected4);
    }
}
