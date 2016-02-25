<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;

class AttributeGroupStandardConverterSpec extends ObjectBehavior
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

    function it_convertes_an_item_to_standard_format()
    {
        $item = [
            'code'        => 'sizes',
            'sort_order'  => 1,
            'attributes'  => 'size,main_color',
            'label-en_US' => 'Sizes',
            'label-fr_FR' => 'Tailles'
        ];

        $result = [
            'code'       => 'sizes',
            'sort_order' => 1,
            'attributes' => ['size', 'main_color'],
            'label'      => [
                'en_US' => 'Sizes',
                'fr_FR' => 'Tailles'
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
