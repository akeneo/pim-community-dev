<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;

class SimpleSelectConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_simpleselect_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('provider', null, null)
            ->willReturn('provider');

        $expected = ['provider' => 'Amazon'];

        $data = [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => 'Amazon',
            ]
        ];

        $this->convert('provider', $data)->shouldReturn($expected);
    }
}
