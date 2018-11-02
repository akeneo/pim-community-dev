<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;

class PriceConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_price_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('super_price', 'fr_FR', 'ecommerce')
            ->willReturn('super_price-fr_FR-ecommerce');

        $columnsResolver->resolveFlatAttributeName('super_price', 'de_DE', 'ecommerce')
            ->willReturn('super_price-de_DE-ecommerce');

        $expected = [
            'super_price-de_DE-ecommerce-EUR' => '10',
            'super_price-de_DE-ecommerce-USD' => '9',
            'super_price-fr_FR-ecommerce-EUR' => '30',
            'super_price-fr_FR-ecommerce-USD' => '29',
        ];

        $data = [
            [
                'locale' => 'de_DE',
                'scope'  => 'ecommerce',
                'data'   => [
                    [
                        'amount'   => '10',
                        'currency' => 'EUR'
                    ],
                    [
                        'amount'   => '9',
                        'currency' => 'USD'
                    ],
                ]
            ],
            [
                'locale' => 'fr_FR',
                'scope'  => 'ecommerce',
                'data'   => [
                    [
                        'amount'   => '30',
                        'currency' => 'EUR'
                    ],
                    [
                        'amount'   => '29',
                        'currency' => 'USD'
                    ],
                ]
            ]
        ];

        $this->convert('super_price', $data)->shouldReturn($expected);
    }
}
