<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

class PriceConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_price']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement(ValueConverterInterface::class);
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_price')->shouldReturn(true);
        $this->supportsField('pim_catalog_number')->shouldReturn(false);
    }

    function it_does_not_convert_when_only_data_is_provided($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = [
            'attribute'      => $attribute,
            'locale_code'    => 'en_US',
            'scope_code'     => 'mobile',
            'price_currency' => 'EUR'
        ];

        $value = '10.00';

        $fieldSplitter->splitPrices($value)->willReturn(['10']);
        $fieldSplitter->splitUnitValue('10')->willReturn([null, null]);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => [['amount' => null, 'currency' => null]],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_returns_empty_data_if_empty_value_provided(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = '';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => [],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_when_only_data_is_provided($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = [
            'attribute'      => $attribute,
            'locale_code'    => 'en_US',
            'scope_code'     => 'mobile',
            'price_currency' => 'EUR'
        ];

        $value = '10.00';

        $fieldSplitter->splitPrices($value)->willReturn(['10']);
        $fieldSplitter->splitUnitValue('10')->willReturn([null, null]);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => [['amount' => null, 'currency' => null]],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_integer_value_formatted_as_string($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(false);
        $fieldNameInfo = [
            'attribute'      => $attribute,
            'locale_code'    => 'en_US',
            'scope_code'     => 'mobile',
            'price_currency' => 'EUR'
        ];

        $value = '10 EUR';

        $fieldSplitter->splitPrices($value)->willReturn(['10 EUR']);
        $fieldSplitter->splitUnitValue('10 EUR')->willReturn(['10', 'EUR']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => [['amount' => 10, 'currency' => 'EUR']],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_decimal_value($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = [
            'attribute'      => $attribute,
            'locale_code'    => 'en_US',
            'scope_code'     => 'mobile',
            'price_currency' => 'EUR'
        ];

        $value = '10.50 EUR';

        $fieldSplitter->splitPrices($value)->willReturn(['10.50 EUR']);
        $fieldSplitter->splitUnitValue('10.50 EUR')->willReturn(['10.50', 'EUR']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => [['amount' => '10.50', 'currency' => 'EUR']],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_french_decimal_value($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = [
            'attribute'      => $attribute,
            'locale_code'    => 'en_US',
            'scope_code'     => 'mobile',
            'price_currency' => 'EUR'
        ];

        $value = '10,55 EUR';

        $fieldSplitter->splitPrices($value)->willReturn(['10,55 EUR']);
        $fieldSplitter->splitUnitValue('10,55 EUR')->willReturn(['10,55', 'EUR']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => [['amount' => '10,55', 'currency' => 'EUR']],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }
}
