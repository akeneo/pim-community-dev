<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class AssociationTypeSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'        => 'long_sword',
            'label-fr_FR' => 'Épée longue',
            'label-en_US' => 'Long sword',
            'is_two_way' => 1
        ];

        $item = [
            'code'   => 'long_sword',
            'labels' => [
                'fr_FR' => 'Épée longue',
                'en_US' => 'Long sword'
            ],
            'is_two_way' => true
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
