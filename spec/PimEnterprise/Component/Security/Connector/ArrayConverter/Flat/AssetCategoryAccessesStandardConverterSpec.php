<?php

namespace spec\PimEnterprise\Component\Security\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;

class AssetCategoryAccessesStandardConverterSpec extends ObjectBehavior
{
    function let(FieldsRequirementValidator $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface'
        );
    }

    function it_converts_an_item_to_standard_format()
    {

        $item = [
            'category'   => 'videos',
            'view_items' => 'IT support,Manager',
            'edit_items' => 'IT support',
            'own_items'  => '',
        ];

        $result = [
            [
                'category'   => 'videos',
                'userGroup'  => 'IT support',
                'view_items' => true,
                'edit_items' => true,
                'own_items'  => false,
            ], [
                'category'   => 'videos',
                'userGroup'  => 'Manager',
                'view_items' => true,
                'edit_items' => false,
                'own_items'  => false,
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
