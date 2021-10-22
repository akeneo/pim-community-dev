<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\MetricToStringDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class MetricToStringDataConverterSpec extends ObjectBehavior
{
    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MetricToStringDataConverter::class);
    }

    function it_only_supports_metric_source_and_string_target(
        AttributeInterface $weight,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $weightInGrams
    ) {
        $weight->getType()->willReturn(AttributeTypes::METRIC);
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $description->getType()->willReturn(AttributeTypes::TEXTAREA);
        $weightInGrams->getType()->willReturn(AttributeTypes::NUMBER);

        $this->supportsAttributes($weight, $name)->shouldReturn(true);
        $this->supportsAttributes($weight, $description)->shouldReturn(true);

        $this->supportsAttributes($weight, $weightInGrams)->shouldReturn(false);
        $this->supportsAttributes($name, $weight)->shouldReturn(false);
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

    function it_converts_a_metric_to_a_string()
    {
        $sourceValue = MetricValue::value(
            'weight_in_grams',
            new Metric('weight', 'GRAM', 23.55, 'KILOGRAM', .02355)
        );

        $this->convert($sourceValue, new Attribute())->shouldReturn('23.55 GRAM');
    }

    function it_converts_a_metric_to_a_string_when_data_comes_from_db()
    {
        $sourceValue = MetricValue::value(
            'weight_in_grams',
            new Metric('weight', 'GRAM', "23.550000", 'KILOGRAM', .02355)
        );

        $this->convert($sourceValue, new Attribute())->shouldReturn('23.55 GRAM');
    }
}
