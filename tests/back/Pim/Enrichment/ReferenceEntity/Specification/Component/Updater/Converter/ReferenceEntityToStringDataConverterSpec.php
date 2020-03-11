<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Converter\ReferenceEntityToStringDataConverter;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;

class ReferenceEntityToStringDataConverterSpec extends ObjectBehavior
{
    function it_is_a_value_data_converter()
    {
        $this->shouldImplement(ValueDataConverter::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityToStringDataConverter::class);
    }

    function it_supports_single_ref_entity_source_and_string_target_attributes(
        AttributeInterface $designers,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $designerCode,
        AttributeInterface $number
    ) {
        $designers->getType()->willReturn(AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT);
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $description->getType()->willReturn(AttributeTypes::TEXTAREA);
        $designerCode->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $number->getType()->willReturn(AttributeTypes::NUMBER);

        $this->supportsAttributes($designers, $name)->shouldReturn(true);
        $this->supportsAttributes($designers, $description)->shouldReturn(true);
        $this->supportsAttributes($designers, $designerCode)->shouldReturn(true);

        $this->supportsAttributes($name, $designers)->shouldReturn(false);
        $this->supportsAttributes($designers, $number)->shouldReturn(false);
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

    function it_converts_areference_entity_single_link_to_a_string()
    {
        $sourceValue = ReferenceEntityValue::value('designers', RecordCode::fromString('starck'));

        $this->convert($sourceValue, new Attribute())->shouldReturn('starck');
    }
}
