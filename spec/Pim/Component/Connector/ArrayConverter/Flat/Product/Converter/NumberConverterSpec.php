<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;

class NumberConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_number']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ConverterInterface');
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_number')->shouldReturn(true);
        $this->supportsField('pim_catalog_media')->shouldReturn(false);
    }

    function it_converts(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 123.45;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => 123.45,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_and_cast_to_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '123123456.254788';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => 123123456.254788,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_returns_null_if_empty_value_provided(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '';

        $this->convert($fieldNameInfo, $value)->shouldReturn(null);
    }
}
