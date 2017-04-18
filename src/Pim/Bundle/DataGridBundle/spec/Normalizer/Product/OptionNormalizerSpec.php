<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;
use Pim\Component\Catalog\ProductValue\OptionProductValueInterface;
use PhpSpec\ObjectBehavior;
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

    function it_supports_datagrid_format_and_product_value(OptionProductValueInterface $productValue)
    {
        $this->supportsNormalization($productValue, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_simple_select_product_value_with_label(
        OptionProductValueInterface $productValue,
        AttributeOptionInterface $color,
        AttributeOptionValueInterface $optionValue
    ) {
        $productValue->getData()->willReturn($color);
        $color->setLocale('fr_FR')->shouldBeCalled();
        $color->getTranslation()->willReturn($optionValue);
        $optionValue->getValue()->willReturn('Violet');
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => 'Violet',
        ];

        $this->normalize($productValue, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }

    function it_normalizes_an_simple_select_product_value_without_label(
        OptionProductValueInterface $productValue,
        AttributeOptionInterface $color,
        AttributeOptionValueInterface $optionValue
    ) {
        $productValue->getData()->willReturn($color);
        $color->setLocale('fr_FR')->shouldBeCalled();
        $color->getTranslation()->willReturn($optionValue);
        $color->getCode()->willReturn('purple');
        $optionValue->getValue()->willReturn(null);
        $optionValue->getValue()->willReturn(null);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => '[purple]',
        ];

        $this->normalize($productValue, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }

    function it_normalizes_a_simple_select_product_value_without_data(
        OptionProductValueInterface $productValue,
        AttributeOptionInterface $color
    ) {
        $productValue->getData()->willReturn(null);
        $color->setLocale(Argument::any())->shouldNotBeCalled();
        $color->getTranslation()->shouldNotBeCalled();
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => '',
        ];

        $this->normalize($productValue, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }
}
