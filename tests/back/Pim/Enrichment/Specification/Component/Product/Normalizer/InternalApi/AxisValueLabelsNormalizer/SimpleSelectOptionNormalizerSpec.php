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

    function it_normalizes_an_option_value_with_a_label(
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

    function it_normalizes_an_option_value_without_a_label(
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

    function it_supports_only_simple_select_option()
    {
        $this->supports(AttributeTypes::OPTION_SIMPLE_SELECT)->shouldReturn(true);
        $this->supports(AttributeTypes::TEXT)->shouldReturn(false);
    }
}
