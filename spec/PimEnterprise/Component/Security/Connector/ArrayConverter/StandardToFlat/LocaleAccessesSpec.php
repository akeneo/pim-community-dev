<?php

namespace spec\PimEnterprise\Component\Security\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class LocaleAccessesSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $item = [
            [
                'locale'        => 'en_US',
                'user_group'    => 'IT support',
                'view_products' => true,
                'edit_products' => true,
            ],
            [
                'locale'        => 'en_US',
                'user_group'    => 'Manager',
                'view_products' => true,
                'edit_products' => false,
            ]
        ];

        $expected = [
            'locale'        => 'en_US',
            'view_products' => 'IT support,Manager',
            'edit_products' => 'IT support',
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
