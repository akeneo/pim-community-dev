<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class SimpleSelectOptionNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    function it_normalizes_an_option_value_with_a_not_empty_label(
        ValueInterface $value,
        $attributeOptionRepository,
        AttributeOptionInterface $option,
        AttributeOptionValueInterface $optionValue
    ) {
        $value->getData()->willReturn('thisrt_color');
        $value->getAttributeCode()->willReturn('color');

        $attributeOptionRepository->findOneByIdentifier('color.thisrt_color')->willReturn($option);
        $option->setLocale('en_US')->shouldBeCalled();
        $option->getTranslation()->willReturn($optionValue);
        $optionValue->getLabel()->willReturn('White');

        $this->normalize($value, 'en_US')->shouldReturn('White');
    }

    function it_normalizes_an_option_value_with_an_empty_string_as_label(
        ValueInterface $value,
        $attributeOptionRepository,
        AttributeOptionInterface $option,
        AttributeOptionValueInterface $optionValue
    ) {
        $value->getData()->willReturn('thisrt_color');
        $value->getAttributeCode()->willReturn('color');

        $attributeOptionRepository->findOneByIdentifier('color.thisrt_color')->willReturn($option);
        $option->setLocale('en_US')->shouldBeCalled();
        $option->getTranslation()->willReturn($optionValue);

        $optionValue->getLabel()->willReturn('');
        $option->getCode()->willReturn('white');
        $this->normalize($value, 'en_US')->shouldReturn('[white]');
    }

    function it_normalizes_an_option_value_with_null_as_label(
        ValueInterface $value,
        $attributeOptionRepository,
        AttributeOptionInterface $option,
        AttributeOptionValueInterface $optionValue
    ) {
        $value->getData()->willReturn('null_color');
        $value->getAttributeCode()->willReturn('color');

        $attributeOptionRepository->findOneByIdentifier('color.null_color')->willReturn($option);
        $option->setLocale('en_US')->shouldBeCalled();
        $option->getTranslation()->willReturn($optionValue);

        $optionValue->getLabel()->willReturn(null);
        $option->getCode()->willReturn('white');
        $this->normalize($value, 'en_US')->shouldReturn('[white]');
    }

    function it_normalizes_an_option_value_with_zero_as_label(
        ValueInterface $value,
        $attributeOptionRepository,
        AttributeOptionInterface $option,
        AttributeOptionValueInterface $optionValue
    ) {
        $value->getData()->willReturn('zero_color');
        $value->getAttributeCode()->willReturn('color');

        $attributeOptionRepository->findOneByIdentifier('color.zero_color')->willReturn($option);
        $option->setLocale('en_US')->shouldBeCalled();
        $option->getTranslation()->willReturn($optionValue);
        $optionValue->getLabel()->willReturn('0');

        $this->normalize($value, 'en_US')->shouldReturn('0');
    }

    function it_supports_only_simple_select_option()
    {
        $this->supports(AttributeTypes::OPTION_SIMPLE_SELECT)->shouldReturn(true);
        $this->supports(AttributeTypes::TEXT)->shouldReturn(false);
    }
}
