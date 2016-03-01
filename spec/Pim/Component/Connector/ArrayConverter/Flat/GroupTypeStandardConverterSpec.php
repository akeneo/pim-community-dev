<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;

class GroupTypeStandardConverterSpec extends ObjectBehavior
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
            'code'        => 'VARIANT',
            'is_variant'  => 1,
            'label-en_US' => 'variant',
            'label-fr_FR' => 'variantes',
        ];

        $result = [
            'code'        => 'VARIANT',
            'is_variant'  => true,
            'label'       => [
                'en_US' => 'variant',
                'fr_FR' => 'variantes',
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
