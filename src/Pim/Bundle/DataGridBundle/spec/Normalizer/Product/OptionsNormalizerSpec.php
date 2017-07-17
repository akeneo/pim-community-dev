<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;
use Pim\Component\Catalog\Value\OptionsValueInterface;

class OptionsNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Normalizer\Product\OptionsNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_format_and_product_value(OptionsValueInterface $value)
    {
        $this->supportsNormalization($value, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_multi_select_product_value(
        OptionsValueInterface $value,
        AttributeOptionInterface $colorBlue,
        AttributeOptionInterface $colorRed,
        AttributeOptionValueInterface $optionValueBlue,
        AttributeOptionValueInterface $optionValueRed
    ) {
        $value->getData()->willReturn([$colorBlue, $colorRed]);
        $colorRed->getTranslation('fr_FR')->willReturn($optionValueRed);
        $colorRed->getCode()->willReturn('red');
        $colorBlue->getTranslation('fr_FR')->willReturn($optionValueBlue);
        $optionValueBlue->getValue()->willReturn('Blue');
        $optionValueRed->getValue()->willReturn(null);
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => 'Blue, [red]',
        ];

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }
}
