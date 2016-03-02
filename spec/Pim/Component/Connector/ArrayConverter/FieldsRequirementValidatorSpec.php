<?php

namespace spec\Pim\Component\Connector\ArrayConverter;

class FieldsRequirementValidatorSpec extends \PhpSpec\ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator');
    }

    function it_does_not_raise_when_there_is_no_required_fields()
    {
        $this
            ->shouldNotThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during('validateFields', [['foo' => 'bar'], []]);
    }

    function it_does_not_raise_when_all_required_fields_are_filled()
    {
        $this
            ->shouldNotThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during('validateFields', [['foo' => 'bar'], ['foo']]);
    }

    function it_should_raise_when_a_required_field_is_blank()
    {
        $this
            ->shouldThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during('validateFields', [['foo' => ''], ['foo']]);
    }

    function it_should_raise_when_a_required_field_is_null()
    {
        $this
            ->shouldThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during('validateFields', [['foo' => null], ['foo']]);
    }

    function it_should_raise_when_a_required_field_is_not_present()
    {
        $this
            ->shouldThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during('validateFields', [['foo' => 'bar'], ['baz']]);
    }
}
