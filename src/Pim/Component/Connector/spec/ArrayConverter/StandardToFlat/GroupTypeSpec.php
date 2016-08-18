<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class GroupTypeSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'        => 'VARIANT',
            'is_variant'  => '1',
            'label-en_US' => 'variant',
            'label-fr_FR' => 'variantes',
        ];

        $item = [
            'code'       => 'VARIANT',
            'is_variant' => true,
            'labels'      => [
                'en_US' => 'variant',
                'fr_FR' => 'variantes',
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
