<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;

class MetricConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_metric']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ConverterInterface');
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_metric')->shouldReturn(true);
    }

    function it_converts(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute'   => $attribute,
                          'locale_code' => 'en_US',
                          'scope_code'  => 'mobile',
                          'metric_unit' => 'EUR'
        ];

        $value = 4.1125;

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['data' => 4.1125, 'unit' => 'EUR'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_and_split_value($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute'   => $attribute,
                          'locale_code' => 'en_US',
                          'scope_code'  => 'mobile'
        ];

        $value = '4.1125 EUR';

        $fieldSplitter->splitUnitValue($value)->willReturn(['4.1125', 'EUR']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['data' => 4.1125, 'unit' => 'EUR'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_converts_and_cast_to_float(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute'   => $attribute,
                          'locale_code' => 'en_US',
                          'scope_code'  => 'mobile',
                          'metric_unit' => 'EUR'
        ];

        $value = '4.1125';

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['data' => 4.1125, 'unit' => 'EUR'],
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
