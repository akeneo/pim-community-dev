<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\CollectionToArrayDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class CollectionToArrayDataConverterSpec extends ObjectBehavior
{
    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CollectionToArrayDataConverter::class);
    }

    function it_only_supports_collection_attributes(
        AttributeInterface $colors,
        AttributeInterface $designers,
        AttributeInterface $name
    ) {
        $colors->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);
        $designers->getType()->willReturn(AttributeTypes::REFERENCE_ENTITY_COLLECTION);
        $name->getType()->willReturn(AttributeTypes::TEXT);

        $this->supportsAttributes($colors, $designers)->shouldReturn(true);
        $this->supportsAttributes($designers, $colors)->shouldReturn(true);

        $this->supportsAttributes($colors, $name)->shouldReturn(false);
        $this->supportsAttributes($name, $designers)->shouldReturn(false);

    }

    function it_throws_an_exception_if_source_data_is_not_iterable()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'convert',
            [
                ScalarValue::value('invalid', 123),
                new Attribute(),
            ]
        );
    }

    function it_converts_an_array_to_an_array()
    {
        $sourceValue = OptionsValue::value('colors', ['red', 'green', 'blue']);

        $this->convert($sourceValue, new Attribute())->shouldReturn(['red', 'green', 'blue']);
    }
}
