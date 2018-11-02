<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class CategorySpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'        => 'armors',
            'parent'      => '',
            'label-fr_FR' => 'Armures',
            'label-en_US' => 'Armors',
        ];

        $item = [
            'code'   => 'armors',
            'parent' => null,
            'labels' => [
                'fr_FR' => 'Armures',
                'en_US' => 'Armors',
            ],
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
