<?php

namespace spec\PimEnterprise\Component\Security\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class AttributeGroupAccessesSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $item = [
            [
                'attribute_group' => 'other',
                'user_group'      => 'IT support',
                'view_attributes' => true,
                'edit_attributes' => true,
            ],
            [
                'attribute_group' => 'other',
                'user_group'      => 'Manager',
                'view_attributes' => true,
                'edit_attributes' => false,
            ]
        ];

        $expected = [
            'attribute_group' => 'other',
            'view_attributes' => 'IT support,Manager',
            'edit_attributes' => 'IT support',
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
