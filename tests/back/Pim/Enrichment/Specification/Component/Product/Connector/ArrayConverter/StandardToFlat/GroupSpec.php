<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class GroupSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'        => 'mycode',
            'type'        => 'RELATED',
            'label-en_US' => 'EN Label',
            'label-fr_FR' => 'FR Label',
        ];

        $item = [
            'code'   => 'mycode',
            'type'   => 'RELATED',
            'labels' => [
                'en_US' => 'EN Label',
                'fr_FR' => 'FR Label',
            ],
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
