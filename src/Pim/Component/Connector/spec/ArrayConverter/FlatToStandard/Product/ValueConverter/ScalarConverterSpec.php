<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;

class ScalarConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith(
            $fieldSplitter,
            [
                'pim_catalog_date',
                'pim_catalog_boolean',
                'pim_catalog_number'
            ]
        );
    }

    function it_is_a_converter()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterInterface');
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_date')->shouldReturn(true);
        $this->supportsField('pim_catalog_boolean')->shouldReturn(true);
        $this->supportsField('pim_catalog_number')->shouldReturn(true);
        $this->supportsField('pim_catalog_price')->shouldReturn(false);
    }

    function it_converts_a_date(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '30/03/2016';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => '30/03/2016',
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_a_boolean(AttributeInterface $attribute)
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

    function it_converts_a_number(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 1234;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => 1234,
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
