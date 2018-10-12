<?php

namespace Specification\Akeneo\Pim\Permission\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class AttributeGroupAccessesSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            ArrayConverterInterface::class
        );
    }

    function it_converts_an_item_to_standard_format()
    {
        $item = [
             'attribute_group' => 'other',
             'view_attributes' => 'IT support,Manager',
             'edit_attributes' => 'IT support',
        ];

        $result = [
            [
                'attribute_group' => 'other',
                'user_group'      => 'IT support',
                'view_attributes' => true,
                'edit_attributes' => true,
            ], [
                'attribute_group' => 'other',
                'user_group'      => 'Manager',
                'view_attributes' => true,
                'edit_attributes' => false,
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
