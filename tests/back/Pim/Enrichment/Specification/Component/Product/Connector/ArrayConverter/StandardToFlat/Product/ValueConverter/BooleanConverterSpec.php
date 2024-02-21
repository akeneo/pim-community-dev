<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;

class BooleanConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_boolean_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('auto_lock', 'fr_FR', null)
            ->willReturn('auto_lock-fr_FR');

        $columnsResolver->resolveFlatAttributeName('auto_lock', 'de_DE', null)
            ->willReturn('auto_lock-de_DE');

        $columnsResolver->resolveFlatAttributeName('auto_lock', 'en_US', null)
            ->willReturn('auto_lock-en_US');

        $expected = [
            'auto_lock-fr_FR' => '1',
            'auto_lock-de_DE' => '0',
            'auto_lock-en_US' => '',
        ];

        $data = [
            [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => true,
            ],
            [
                'locale' => 'de_DE',
                'scope'  => null,
                'data'   => false,
            ],
            [
                'locale' => 'en_US',
                'scope' => null,
                'data' => null,
            ],
        ];

        $this->convert('auto_lock', $data)->shouldReturn($expected);
    }
}
