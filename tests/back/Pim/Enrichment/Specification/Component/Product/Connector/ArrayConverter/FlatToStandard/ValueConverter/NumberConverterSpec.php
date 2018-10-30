<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

class NumberConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_number']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement(ValueConverterInterface::class);
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_number')->shouldReturn(true);
        $this->supportsField('pim_catalog_price')->shouldReturn(false);
    }

    function it_converts_an_integer(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(false);
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 1234;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => 1234,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_an_integer_formatted_as_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(false);
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '1234';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => 1234,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_does_not_convert_a_float(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 1234.36;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => 1234.36,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }
}
