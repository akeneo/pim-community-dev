<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Prophecy\Argument;

class LocaleStandardConverterSpec extends ObjectBehavior
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

    function it_converts_an_item_to_standard_format($validator)
    {
        $validator->validateFields(Argument::any(), ['code'])->shouldBeCalled();

        $this->convert(['code' => 'en_US', 'foo' => 'bar'])
            ->shouldReturn(['code' => 'en_US']);
    }
}
