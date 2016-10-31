<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\AttributeOptionNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(AttributeOptionInterface $attributeOption)
    {
        $this->supportsNormalization($attributeOption, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($attributeOption, 'xml')->shouldReturn(false);
        $this->supportsNormalization($attributeOption, 'json')->shouldReturn(false);
    }

    function it_normalizes_an_attribute_option_without_label(
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute
    ) {
        $attributeOption->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $attributeOption->getCode()->willReturn('red');
        $attributeOption->getOptionValues()->willReturn([]);
        $attributeOption->getSortOrder()->willReturn(1);

        $this->normalize($attributeOption, 'standard')->shouldReturn([
            'code' => 'red',
            'attribute' => 'color',
            'sort_order' => 1,
            'labels' => []
        ]);
    }

    function it_normalizes_an_attribute_option(
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute,
        AttributeOptionValueInterface $valueEn,
        AttributeOptionValueInterface $valueFr
    ) {
        $attributeOption->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $attributeOption->getCode()->willReturn('red');
        $attributeOption->getOptionValues()->willReturn([
            'en_US' => $valueEn,
            'fr_FR' => $valueFr,
        ]);
        $attributeOption->getSortOrder()->willReturn(1);
        $valueEn->getLocale()->willReturn('en_US');
        $valueEn->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');

        $this->normalize($attributeOption, 'standard', ['locales' => ['en_US', 'fr_FR', 'de_DE']])->shouldReturn([
            'code' => 'red',
            'attribute' => 'color',
            'sort_order' => 1,
            'labels' => [
                'en_US' => 'Red',
                'fr_FR' => 'Rouge',
                'de_DE' => null
            ]
        ]);
    }
}
