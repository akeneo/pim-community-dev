<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\StringToStringDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class StringToStringDataConverterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(StringToStringDataConverter::class);
    }

    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_supports_a_combination_of_source_and_target_attributes(
        AttributeInterface $sku,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $color,
        AttributeInterface $designer,
        AttributeInterface $weight
    ) {
        $sku->getType()->willReturn(AttributeTypes::IDENTIFIER);
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $description->getType()->willReturn(AttributeTypes::TEXTAREA);
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $designer->getType()->willReturn(AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT);
        $weight->getType()->willReturn(AttributeTypes::METRIC);

        $this->supportsAttributes($sku, $name)->shouldReturn(true);
        $this->supportsAttributes($sku, $description)->shouldReturn(true);
        $this->supportsAttributes($name, $description)->shouldReturn(true);
        $this->supportsAttributes($name, $color)->shouldReturn(true);
        $this->supportsAttributes($name, $designer)->shouldReturn(true);
        $this->supportsAttributes($color, $name)->shouldReturn(true);
        $this->supportsAttributes($color, $description)->shouldReturn(true);
        $this->supportsAttributes($color, $designer)->shouldReturn(true);

        $this->supportsAttributes($designer, $name)->shouldReturn(false);
        $this->supportsAttributes($designer, $description)->shouldReturn(false);
        $this->supportsAttributes($designer, $color)->shouldReturn(false);
        $this->supportsAttributes($name, $weight)->shouldReturn(false);
        $this->supportsAttributes($weight, $name)->shouldReturn(false);
    }

    function it_throws_an_exception_if_source_data_is_not_a_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'convert',
            [
                ScalarValue::value('test', 123),
                new Attribute(),
            ]
        );
    }

    function it_returns_a_string_data()
    {
        $sourceValue = ScalarValue::value('name', 'My awesome product');

        $this->convert($sourceValue, new Attribute())->shouldReturn('My awesome product');
    }
}
