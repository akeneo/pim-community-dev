<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;

class DateConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_date_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('release_date', 'fr_FR', null)
            ->willReturn('release_date-fr_FR');

        $expected = ['release_date-fr_FR' => '2016-02-22'];

        $data = [
            [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => '2016-02-22',
            ]
        ];

        $this->convert('release_date', $data)->shouldReturn($expected);
    }
}
