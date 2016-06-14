<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product;

class ProductDraftSpec extends ObjectBehavior
{
    function let(Product $productConverter)
    {
        $this->beConstructedWith($productConverter);
    }

    function it_converts_from_standard_to_flat_format($productConverter)
    {
        $expected = [
            'sku'                      => 'MySku',
            'name-en_US'               => 'Ziggy who, Ziggy what',
            'description-en_US-mobile' => 'My description',
            'length'                   => '10',
            'length-unit'              => 'CENTIMETER',
        ];

        $item = [
            'sku' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'MySku'
                ]
            ],
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'Ziggy who, Ziggy what'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' => 'mobile',
                    'data' => 'My description'
                ]
            ],
            'length' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => [
                        'data' => '10',
                        'unit' => 'CENTIMETER'
                    ]
                ]
            ]
        ];

        $productConverter->convert($item, ['foo' => 'bar'])->willReturn($expected);

        $this->convert($item, ['foo' => 'bar'])->shouldReturn($expected);
    }
}
