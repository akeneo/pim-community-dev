<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;

class BooleanConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_boolean']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ValueConverterInterface');
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_boolean')->shouldReturn(true);
    }

    function it_converts(AttributeInterface $attribute)
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

    function it_converts_and_cast_to_boolean(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 'true';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => true,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_returns_null_data_if_empty_value_provided(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => null,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }
}
