<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;

class PriceConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['pim_catalog_price']);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ValueConverterInterface');
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_price')->shouldReturn(true);
        $this->supportsField('pim_catalog_number')->shouldReturn(false);
    }

    function it_converts($fieldSplitter, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $fieldNameInfo = ['attribute'      => $attribute,
                          'locale_code'    => 'en_US',
                          'scope_code'     => 'mobile',
                          'price_currency' => 'EUR'
        ];

        $value = '10.00';

        $fieldSplitter->splitCollection($value)->willReturn(['10']);

        $expectedResult = ['attribute_code' => [[
            'locale' => 'en_US',
            'scope'  => 'mobile',
            'data'   => [['data' => 10.00, 'currency' => 'EUR']],
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
