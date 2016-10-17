<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;

class BooleanConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_boolean']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterInterface');
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_boolean')->shouldReturn(true);
        $this->supportsField('pim_catalog_price')->shouldReturn(false);
    }

    function it_converts_false(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = false;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => false,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_a_zero(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 0;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => false,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_a_zero_formatted_as_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '0';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => false,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_true(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = true;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => true,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_a_one(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 1;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => true,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_a_one_formatted_as_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '1';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => true,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }
}
