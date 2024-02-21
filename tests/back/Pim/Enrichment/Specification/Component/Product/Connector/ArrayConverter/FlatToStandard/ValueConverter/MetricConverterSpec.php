<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

class MetricConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_metric']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement(ValueConverterInterface::class);
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_metric')->shouldReturn(true);
    }

    function it_does_not_convert_when_only_data_is_provided(AttributeInterface $attribute, $fieldSplitter)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = [
            'attribute'   => $attribute,
            'locale_code' => 'en_US',
            'scope_code'  => 'mobile',
            'metric_unit' => 'GRAM'
        ];

        $value = 4.1125;
        $fieldSplitter->splitUnitValue($value)->willReturn(['4.1125']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['amount' => '4.1125', 'unit' => null],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_and_split_value($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = [
            'attribute'   => $attribute,
            'locale_code' => 'en_US',
            'scope_code'  => 'mobile'
        ];

        $value = '4.1125 GRAM';

        $fieldSplitter->splitUnitValue($value)->willReturn(['4.1125', 'GRAM']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['amount' => '4.1125', 'unit' => 'GRAM'],
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
            'data'   => null,
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_returns_no_amount_if_only_unit_provided($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];

        $value = 'GRAM';

        $fieldSplitter->splitUnitValue($value)->willReturn(['GRAM']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['amount' => null, 'unit' => 'GRAM'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_integer_value($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(false);
        $fieldNameInfo = [
            'attribute'   => $attribute,
            'locale_code' => 'en_US',
            'scope_code'  => 'mobile'
        ];

        $value = '41125 GRAM';

        $fieldSplitter->splitUnitValue($value)->willReturn(['41125', 'GRAM']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['amount' => 41125, 'unit' => 'GRAM'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_decimal_value($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = [
            'attribute'   => $attribute,
            'locale_code' => 'en_US',
            'scope_code'  => 'mobile'
        ];

        $value = '4.1125 GRAM';

        $fieldSplitter->splitUnitValue($value)->willReturn(['4.1125', 'GRAM']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['amount' => '4.1125', 'unit' => 'GRAM'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_french_decimal_value($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $fieldNameInfo = [
            'attribute'   => $attribute,
            'locale_code' => 'en_US',
            'scope_code'  => 'mobile'
        ];

        $value = '4,1125 GRAM';

        $fieldSplitter->splitUnitValue($value)->willReturn(['4,1125', 'GRAM']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['amount' => '4,1125', 'unit' => 'GRAM'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }
}
