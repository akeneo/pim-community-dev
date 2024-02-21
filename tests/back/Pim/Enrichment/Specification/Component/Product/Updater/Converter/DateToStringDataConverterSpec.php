<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\DateToStringDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class DateToStringDataConverterSpec extends ObjectBehavior
{
    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateToStringDataConverter::class);
    }

    function it_supports_date_attributes(
        AttributeInterface $releaseDate,
        AttributeInterface $brand,
        AttributeInterface $description,
        AttributeInterface $weight
    ) {
        $releaseDate->getType()->willReturn(AttributeTypes::DATE);
        $brand->getType()->willReturn(AttributeTypes::TEXT);
        $description->getType()->willReturn(AttributeTypes::TEXTAREA);
        $weight->getType()->willReturn(AttributeTypes::METRIC);

        $this->supportsAttributes($releaseDate, $brand)->shouldReturn(true);
        $this->supportsAttributes($releaseDate, $description)->shouldReturn(true);

        $this->supportsAttributes($brand, $releaseDate)->shouldReturn(false);
        $this->supportsAttributes($releaseDate, $weight)->shouldReturn(false);
    }

    function it_throws_an_exception_if_source_data_is_not_a_date()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'convert',
            [
                ScalarValue::value('invalid', 123),
                new Attribute(),
            ]
        );
    }

    function it_converts_a_date_to_a_string()
    {
        $sourceValue = DateValue::value(
            'release_date',
            new \DateTime('2012-12-21', new \DateTimeZone('Europe/Paris'))
        );

        $this->convert($sourceValue, new Attribute())->shouldReturn('2012-12-21T00:00:00+01:00');
    }
}
