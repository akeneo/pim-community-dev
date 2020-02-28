<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverterRegistry;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class ValueDataConverterRegistrySpec extends ObjectBehavior
{
    function let(
        ValueDataConverter $firstConverter,
        ValueDataConverter $secondConverter
    ) {
        $this->beConstructedWith([$firstConverter, $secondConverter]);
    }

    function it_is_a_value_data_converter_registry()
    {
        $this->shouldHaveType(ValueDataConverterRegistry::class);
    }

    function it_gets_a_value_data_converter(
        ValueDataConverter $firstConverter,
        ValueDataConverter $secondConverter,
        AttributeInterface $sourceAttribute,
        AttributeInterface $targetAttribute
    ) {
        $firstConverter->supportsAttributes($sourceAttribute, $targetAttribute)->willReturn(false);
        $secondConverter->supportsAttributes($sourceAttribute, $targetAttribute)->willReturn(true);

        $this->getDataConverter($sourceAttribute, $targetAttribute)->shouldReturn($secondConverter);
    }

    function it_returns_null_if_attributes_are_not_supported(
        ValueDataConverter $firstConverter,
        ValueDataConverter $secondConverter,
        AttributeInterface $sourceAttribute,
        AttributeInterface $targetAttribute
    ) {
        $firstConverter->supportsAttributes($sourceAttribute, $targetAttribute)->willReturn(false);
        $secondConverter->supportsAttributes($sourceAttribute, $targetAttribute)->willReturn(false);

        $this->getDataConverter($sourceAttribute, $targetAttribute)->shouldReturn(null);
    }
}
