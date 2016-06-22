<?php

namespace spec\PimEnterprise\Component\Security\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class ProductCategoryAccessesSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $item = [
            [
                'category'   => '2013_collection',
                'user_group' => 'IT support',
                'view_items' => true,
                'edit_items' => true,
                'own_items'  => false,
            ],
            [
                'category'   => '2013_collection',
                'user_group' => 'Manager',
                'view_items' => true,
                'edit_items' => false,
                'own_items'  => false,
            ]
        ];

        $expected = [
            'category'   => '2013_collection',
            'view_items' => 'IT support,Manager',
            'edit_items' => 'IT support',
            'own_items'  => '',
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
