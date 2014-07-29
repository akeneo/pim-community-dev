<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_csv_normalization_of_attribute_option(AttributeOption $option)
    {
        $this->supportsNormalization($option, 'csv')->shouldBe(true);
    }

    function it_supports_flat_normalization_of_attribute_option(AttributeOption $option)
    {
        $this->supportsNormalization($option, 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_option_code_when_field_name_is_provided(AttributeOption $option)
    {
        $option->getCode()->willReturn('red');

        $this->normalize($option, null, ['field_name' => 'color'])->shouldReturn(['color' => 'red']);
    }

    function it_normalizes_the_whole_option(
        AttributeOption $option,
        AbstractAttribute $attribute,
        AttributeOptionValue $valueEn,
        AttributeOptionValue $valueFr
    ) {
        $option->getCode()->willReturn('red');
        $option->getAttribute()->willReturn($attribute);
        $option->isDefault()->willReturn(true);
        $attribute->getCode()->willReturn('color');
        $option->getOptionValues()->willReturn([
            'en_US' => $valueEn,
            'fr_FR' => $valueFr,
        ]);
        $valueEn->getLocale()->willReturn('en_US');
        $valueEn->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');

        $this->normalize($option, null, ['locales' => ['en_US', 'fr_FR', 'de_DE']])->shouldReturn([
            'attribute' => 'color',
            'code' => 'red',
            'default' => '1',
            'label-en_US' => 'Red',
            'label-fr_FR' => 'Rouge',
            'label-de_DE' => '',
        ]);
    }
}
