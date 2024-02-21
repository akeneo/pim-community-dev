<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\NumberToMetricDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class NumberToMetricDataConverterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NumberToMetricDataConverter::class);
    }

    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_only_supports_number_and_metric_attribute_types(
        AttributeInterface $number,
        AttributeInterface $weight,
        AttributeInterface $name
    ) {
        $number->getType()->willReturn(AttributeTypes::NUMBER);
        $weight->getType()->willReturn(AttributeTypes::METRIC);
        $name->getType()->willReturn(AttributeTypes::TEXT);

        $this->supportsAttributes($number, $weight)->shouldReturn(true);

        $this->supportsAttributes($weight, $number)->shouldReturn(false);
        $this->supportsAttributes($number, $name)->shouldReturn(false);
        $this->supportsAttributes($name, $weight)->shouldReturn(false);
    }

    function it_throws_an_exception_if_source_data_is_not_numeric()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'convert',
            [
                ScalarValue::value('name', 'Super product'),
                new Attribute(),
            ]
        );
    }

    function it_converts_a_number_to_a_metric(AttributeInterface $weight)
    {
        $sourceValue = ScalarValue::value('number', 10.5);
        $weight->getMetricFamily()->willReturn('weight');
        $weight->getDefaultMetricUnit()->willReturn('GRAM');

        $this->convert($sourceValue, $weight)->shouldReturn([
            'amount' => 10.5,
            'unit' => 'GRAM',
        ]);
    }
}
