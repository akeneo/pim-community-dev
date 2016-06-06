<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class ChannelSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'       => 'tavern',
            'label'      => 'Tavern',
            'locales'    => '',
            'currencies' => 'GLD,PST',
            'tree'       => 'master_catalog',
            'color'      => 'orange'
        ];

        $item = [
            'code'       => 'tavern',
            'label'      => 'Tavern',
            'locales'    => [],
            'currencies' => [
                'GLD',
                'PST'
            ],
            'tree'       => 'master_catalog',
            'color'      => 'orange'
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
