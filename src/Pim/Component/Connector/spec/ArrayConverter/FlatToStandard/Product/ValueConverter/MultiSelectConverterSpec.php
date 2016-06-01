<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;

class MultiSelectConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_multiselect', 'pim_reference_data_multiselect']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterInterface');
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_multiselect')->shouldReturn(true);
        $this->supportsField('pim_reference_data_multiselect')->shouldReturn(true);
        $this->supportsField('pim_catalog_metric')->shouldReturn(false);
    }

    function it_converts($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = [
            'attribute'   => $attribute,
            'locale_code' => 'en_US',
            'scope_code'  => 'mobile'
        ];

        $value = 'dry,wet';

        $fieldSplitter->splitCollection($value)->willReturn(['dry', 'wet']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => ['dry', 'wet'],
        ]]];

        $this->convert($fieldNameInfo, $value)->shouldReturn($expectedResult);
    }

    function it_returns_empty_data_if_empty_value_provided(AttributeInterface $attribute)
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
