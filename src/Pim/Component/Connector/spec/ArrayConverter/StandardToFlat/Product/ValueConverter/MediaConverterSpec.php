<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;

class MediaConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_media_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('picture', 'fr_FR', null)
            ->willReturn('picture-fr_FR');

        $expected = ['picture-fr_FR' => 'x5/78/87/sdqdsqf654qsd6f5465sdqfsqdf65_toto.jpg'];

        $data = [
            [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => ['filePath' => 'x5/78/87/sdqdsqf654qsd6f5465sdqfsqdf65_toto.jpg'],
            ]
        ];

        $this->convert('picture', $data)->shouldReturn($expected);
    }
}
