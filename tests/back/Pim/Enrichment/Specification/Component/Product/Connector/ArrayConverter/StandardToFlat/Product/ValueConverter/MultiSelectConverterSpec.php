<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;

class MultiSelectConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_multiselect_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('colors', 'de_DE', 'ecommerce')
            ->willReturn('colors-de_DE-ecommerce');

        $columnsResolver->resolveFlatAttributeName('colors', 'fr_FR', 'ecommerce')
            ->willReturn('colors-fr_FR-ecommerce');

        $expected = [
            'colors-de_DE-ecommerce' => 'blue,yellow,red',
            'colors-fr_FR-ecommerce' => '',
        ];

        $data = [
            [
                'locale' => 'de_DE',
                'scope'  => 'ecommerce',
                'data'   => ['blue', 'yellow', 'red']
            ],
            [
                'locale' => 'fr_FR',
                'scope'  => 'ecommerce',
                'data'   => []
            ]
        ];

        $this->convert('colors', $data)->shouldReturn($expected);
    }
}
