<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;

class NumberConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_number_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('score', null, null)
            ->willReturn('score');

        $expected = ['score' => 12.50];

        $data = [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => '12.50',
            ]
        ];

        $this->convert('score', $data)->shouldReturn($expected);
    }
}
