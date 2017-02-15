<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;

class TextCollectionConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_text_collection_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('my_collection', null, 'mobile')
            ->willReturn('my_collection-mobile');

        $expected = ['my_collection-mobile' => 'foo,bar;baz;"snafu"'];

        $data = [
            [
                'locale' => null,
                'scope'  => 'mobile',
                'data'   => ['foo,bar','baz','"snafu"'],
            ]
        ];

        $this->convert('my_collection', $data)->shouldReturn($expected);
    }
}
