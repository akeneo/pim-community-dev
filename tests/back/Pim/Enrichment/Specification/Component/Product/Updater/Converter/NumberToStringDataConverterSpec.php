<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\NumberToStringDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class NumberToStringDataConverterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NumberToStringDataConverter::class);
    }

    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_only_supports_number_and_text_attributes(
        AttributeInterface $numberOfCPUs,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $weight
    ) {
        $numberOfCPUs->getType()->willReturn(AttributeTypes::NUMBER);
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $description->getType()->willReturn(AttributeTypes::TEXTAREA);
        $weight->getType()->willReturn(AttributeTypes::METRIC);

        $this->supportsAttributes($numberOfCPUs, $name)->shouldReturn(true);
        $this->supportsAttributes($numberOfCPUs, $description)->shouldReturn(true);

        $this->supportsAttributes($numberOfCPUs, $weight)->shouldReturn(false);
        $this->supportsAttributes($name, $numberOfCPUs)->shouldReturn(false);
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

    function it_converts_a_number_value_to_a_string()
    {
        $this->convert(ScalarValue::value('number', 4.578), new Attribute())->shouldReturn('4.578');
    }
}
