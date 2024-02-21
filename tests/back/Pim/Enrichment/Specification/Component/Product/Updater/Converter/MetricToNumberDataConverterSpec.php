<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\MetricToNumberDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class MetricToNumberDataConverterSpec extends ObjectBehavior
{
    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MetricToNumberDataConverter::class);
    }

    function it_only_supports_metric_source_and_number_target(
        AttributeInterface $weight,
        AttributeInterface $weightInGrams,
        AttributeInterface $name
    ) {
        $weight->getType()->willReturn(AttributeTypes::METRIC);
        $weightInGrams->getType()->willReturn(AttributeTypes::NUMBER);
        $name->getType()->willReturn(AttributeTypes::TEXT);

        $this->supportsAttributes($weight, $weightInGrams)->shouldReturn(true);

        $this->supportsAttributes($weight, $name)->shouldReturn(false);
        $this->supportsAttributes($weightInGrams, $weight)->shouldReturn(false);
    }

    function it_throws_an_exception_if_source_data_is_not_a_metric()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'convert',
            [
                ScalarValue::value('invalid', 123),
                new Attribute(),
            ]
        );
    }

    function it_converts_a_metric_to_a_number()
    {
        $sourceValue = MetricValue::value(
            'weight_in_grams',
            new Metric('weight', 'GRAM', 23.5, 'KILOGRAM', .0235)
        );

        $this->convert($sourceValue, new Attribute())->shouldReturn(23.5);
    }
}
