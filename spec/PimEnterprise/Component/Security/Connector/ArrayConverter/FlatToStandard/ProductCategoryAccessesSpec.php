<?php

namespace spec\PimEnterprise\Component\Security\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class ProductCategoryAccessesSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\ArrayConverterInterface'
        );
    }

    function it_converts_an_item_to_standard_format()
    {
        $item = [
            'category'   => '2013_collection',
            'view_items' => 'IT support,Manager',
            'edit_items' => 'IT support',
            'own_items'  => '',
        ];

        $result = [
            [
                'category'   => '2013_collection',
                'user_group' => 'IT support',
                'view_items' => true,
                'edit_items' => true,
                'own_items'  => false,
            ], [
                'category'   => '2013_collection',
                'user_group' => 'Manager',
                'view_items' => true,
                'edit_items' => false,
                'own_items'  => false,
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
