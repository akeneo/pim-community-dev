<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class AttributeOptionSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'attribute'   => 'armor_material',
            'code'        => 'leather',
            'sort_order'  => '2',
            'label-de_DE' => 'Leder',
            'label-en_US' => 'Leather',
            'label-fr_FR' => 'Cuir'
        ];

        $item = [
            'attribute'  => 'armor_material',
            'code'       => 'leather',
            'sort_order' => 2,
            'labels'     => [
                'de_DE' => 'Leder',
                'en_US' => 'Leather',
                'fr_FR' => 'Cuir'
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
