<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\ProductValue\OptionsProductValueInterface;

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

    function it_supports_datagrid_format_and_product_value(OptionsProductValueInterface $productValue)
    {
        $this->supportsNormalization($productValue, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_multi_select_product_value(
        OptionsProductValueInterface $productValue,
        AttributeOptionInterface $colorBlue,
        AttributeOptionInterface $colorRed,
        AttributeOptionValueInterface $optionValueBlue,
        AttributeOptionValueInterface $optionValueRed
    ) {
        $productValue->getData()->willReturn([$colorBlue, $colorRed]);
        $colorRed->getTranslation('fr_FR')->willReturn($optionValueRed);
        $colorRed->getCode()->willReturn('red');
        $colorBlue->getTranslation('fr_FR')->willReturn($optionValueBlue);
        $optionValueBlue->getValue()->willReturn('Blue');
        $optionValueRed->getValue()->willReturn(null);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => 'Blue, [red]',
        ];

        $this->normalize($productValue, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }
}
