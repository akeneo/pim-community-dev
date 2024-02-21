<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;

class TextConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_simpleselect_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('name', null, 'mobile')
            ->willReturn('name-mobile');

        $expected = ['name-mobile' => 'Golden trumpet'];

        $data = [
            [
                'locale' => null,
                'scope'  => 'mobile',
                'data'   => 'Golden trumpet',
            ]
        ];

        $this->convert('name', $data)->shouldReturn($expected);
    }
}
