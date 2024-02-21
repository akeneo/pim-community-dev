<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class AttributeGroupSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'        => 'potions',
            'sort_order'  => '1',
            'attributes'  => 'color,components',
            'label-en_US' => 'The potions',
            'label-fr_FR' => 'Les potions'
        ];

        $item = [
            'code'       => 'potions',
            'sort_order' => 1,
            'attributes' => ['color', 'components'],
            'labels'      => [
                'en_US' => 'The potions',
                'fr_FR' => 'Les potions'
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
