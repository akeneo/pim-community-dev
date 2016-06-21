<?php

namespace spec\Pim\Component\Connector\ArrayConverter;

use PhpSpec\ObjectBehavior;

class FieldsRequirementCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker');
    }

    function it_does_not_raise_exception_when_there_is_no_required_fields()
    {
        $this
            ->shouldNotThrow('Pim\Component\Connector\Exception\StructureArrayConversionException')
            ->during('checkFieldsPresence', [['foo' => 'bar'], []]);
    }

    function it_does_not_raise_exception_when_all_required_fields_are_filled()
    {
        $this
            ->shouldNotThrow('Pim\Component\Connector\Exception\StructureArrayConversionException')
            ->during('checkFieldsPresence', [['foo' => 'bar'], ['foo']]);
    }

    function it_should_raise_exception_when_a_required_field_is_blank()
    {
        $this
            ->shouldThrow('Pim\Component\Connector\Exception\DataArrayConversionException')
            ->during('checkFieldsFilling', [['foo' => ''], ['foo']]);
    }

    function it_should_raise_exception_when_a_required_field_is_null()
    {
        $this
            ->shouldThrow('Pim\Component\Connector\Exception\DataArrayConversionException')
            ->during('checkFieldsFilling', [['foo' => null], ['foo']]);
    }

    function it_should_raise_exception_when_a_required_field_is_not_present()
    {
        $this
            ->shouldThrow('Pim\Component\Connector\Exception\StructureArrayConversionException')
            ->during('checkFieldsPresence', [['foo' => 'bar'], ['baz']]);
    }
}
