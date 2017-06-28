<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;
use Pim\Component\Catalog\Value\OptionValueInterface;
use Prophecy\Argument;

class OptionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Normalizer\Product\OptionNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_format_and_product_value(OptionValueInterface $value)
    {
        $this->supportsNormalization($value, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_simple_select_product_value_with_label(
        OptionValueInterface $value,
        AttributeOptionInterface $color,
        AttributeOptionValueInterface $optionValue
    ) {
        $value->getData()->willReturn($color);
        $color->setLocale('fr_FR')->shouldBeCalled();
        $color->getTranslation()->willReturn($optionValue);
        $optionValue->getValue()->willReturn('Violet');
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => 'Violet',
        ];

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }

    function it_normalizes_an_simple_select_product_value_without_label(
        OptionValueInterface $value,
        AttributeOptionInterface $color,
        AttributeOptionValueInterface $optionValue
    ) {
        $value->getData()->willReturn($color);
        $color->setLocale('fr_FR')->shouldBeCalled();
        $color->getTranslation()->willReturn($optionValue);
        $color->getCode()->willReturn('purple');
        $optionValue->getValue()->willReturn(null);
        $optionValue->getValue()->willReturn(null);
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => '[purple]',
        ];

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }

    function it_normalizes_a_simple_select_product_value_without_data(
        OptionValueInterface $value,
        AttributeOptionInterface $color
    ) {
        $value->getData()->willReturn(null);
        $color->setLocale(Argument::any())->shouldNotBeCalled();
        $color->getTranslation()->shouldNotBeCalled();
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => '',
        ];

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }
}
