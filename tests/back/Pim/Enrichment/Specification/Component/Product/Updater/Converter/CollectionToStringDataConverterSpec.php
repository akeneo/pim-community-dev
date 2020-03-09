<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\CollectionToStringDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class CollectionToStringDataConverterSpec extends ObjectBehavior
{
    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CollectionToStringDataConverter::class);
    }

    function it_supports_collections_source_and_string_target_attributes(
        AttributeInterface $colors,
        AttributeInterface $designers,
        AttributeInterface $brand,
        AttributeInterface $description,
        AttributeInterface $basePrices,
        AttributeInterface $weight
    ) {
        $colors->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);
        $designers->getType()->willReturn(AttributeTypes::REFERENCE_ENTITY_COLLECTION);
        $brand->getType()->willReturn(AttributeTypes::TEXT);
        $description->getType()->willReturn(AttributeTypes::TEXTAREA);
        $basePrices->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $weight->getType()->willReturn(AttributeTypes::METRIC);

        $this->supportsAttributes($colors, $brand)->shouldReturn(true);
        $this->supportsAttributes($colors, $description)->shouldReturn(true);
        $this->supportsAttributes($designers, $brand)->shouldReturn(true);
        $this->supportsAttributes($designers, $description)->shouldReturn(true);
        $this->supportsAttributes($basePrices, $brand)->shouldReturn(true);
        $this->supportsAttributes($basePrices, $description)->shouldReturn(true);

        $this->supportsAttributes($colors, $designers)->shouldReturn(false);
        $this->supportsAttributes($brand, $colors)->shouldReturn(false);
        $this->supportsAttributes($colors, $weight)->shouldReturn(false);
        $this->supportsAttributes($basePrices, $colors)->shouldReturn(false);
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

    function it_converts_an_array_to_a_string()
    {
        $sourceValue = OptionsValue::value('colors', ['red', 'green', 'blue']);

        $this->convert($sourceValue, new Attribute())->shouldReturn('red, green, blue');
    }

    function it_converts_a_price_collection_to_a_string()
    {
        $sourceValue = PriceCollectionValue::value('prices', new PriceCollection([
            new ProductPrice(15.99, 'USD'),
            new ProductPrice(13.99, 'EUR'),
        ]));

        $this->convert($sourceValue, new Attribute())->shouldReturn('15.99 USD, 13.99 EUR');
    }
}
