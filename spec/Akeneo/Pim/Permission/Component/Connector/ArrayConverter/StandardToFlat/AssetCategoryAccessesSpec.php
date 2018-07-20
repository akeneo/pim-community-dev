<?php

namespace spec\Akeneo\Pim\Permission\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class AssetCategoryAccessesSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $item = [
            [
                'category'   => 'videos',
                'user_group' => 'IT support',
                'view_items' => true,
                'edit_items' => true,
                'own_items'  => false,
            ],
            [
                'category'   => 'videos',
                'user_group' => 'Manager',
                'view_items' => true,
                'edit_items' => false,
                'own_items'  => false,
            ]
        ];

        $expected = [
            'category'   => 'videos',
            'view_items' => 'IT support,Manager',
            'edit_items' => 'IT support',
            'own_items'  => '',
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
