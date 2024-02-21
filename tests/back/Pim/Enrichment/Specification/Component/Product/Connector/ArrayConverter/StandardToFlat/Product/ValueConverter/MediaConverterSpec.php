<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;

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
                'data'   => 'x5/78/87/sdqdsqf654qsd6f5465sdqfsqdf65_toto.jpg',
            ]
        ];

        $this->convert('picture', $data)->shouldReturn($expected);
    }
}
