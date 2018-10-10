<?php

namespace Specification\Akeneo\Channel\Component\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class ChannelSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'        => 'tavern',
            'label-en_US' => 'Tavern',
            'label-fr_FR' => 'Taverne',
            'locales'     => '',
            'currencies'  => 'GLD,PST',
            'tree'        => 'master_catalog',
            'color'       => 'orange',
            'conversion_units' => 'weight:KILOGRAM,size:CENTIMETER'
        ];

        $item = [
            'code'       => 'tavern',
            'labels'     => [
                'en_US'  => 'Tavern',
                'fr_FR'  => 'Taverne',
            ],
            'locales'    => [],
            'currencies' => [
                'GLD',
                'PST'
            ],
            'category_tree'   => 'master_catalog',
            'color'           => 'orange',
            'conversion_units' => [
                'weight' => 'KILOGRAM',
                'size'   => 'CENTIMETER'
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
