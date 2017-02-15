<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterInterface;

class TextCollectionConverterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [AttributeTypes::TEXT_COLLECTION]
        );
    }

    function it_is_a_converter()
    {
        $this->shouldImplement(ValueConverterInterface::class);
    }

    function it_supports_converter_field()
    {
        $this->supportsField(AttributeTypes::TEXT_COLLECTION)->shouldReturn(true);
        $this->supportsField(AttributeTypes::TEXT)->shouldReturn(false);
        $this->supportsField(AttributeTypes::TEXTAREA)->shouldReturn(false);
        $this->supportsField(AttributeTypes::PRICE_COLLECTION)->shouldReturn(false);
    }

    function it_converts_a_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 'foo;bar';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['foo','bar'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);

        $value = 'foo,bar;baz';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['foo,bar','baz'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_returns_empty_array_data_if_empty_value_provided(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => [],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }
}
