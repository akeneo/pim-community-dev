<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_xml_normalization_of_attribute_option(AttributeOption $option)
    {
        $this->supportsNormalization($option, 'xml')->shouldBe(true);
    }

    function it_supports_json_normalization_of_attribute_option(AttributeOption $option)
    {
        $this->supportsNormalization($option, 'json')->shouldBe(true);
    }

    function it_does_not_support_json_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'json')->shouldBe(false);
    }

    function it_normalizes_option_code_when_product_entity_is_provided(AttributeOption $option)
    {
        $option->getCode()->willReturn('red');

        $this->normalize($option, null, ['entity' => 'product'])->shouldReturn('red');
    }

    function it_normalizes_the_whole_option(
        AttributeOption $option,
        AbstractAttribute $attribute,
        AttributeOptionValue $valueEn,
        AttributeOptionValue $valueFr
    ) {
        $option->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $option->getCode()->willReturn('red');
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
            'label' => ['en_US' => 'Red', 'fr_FR' => 'Rouge', 'de_DE' => '']
        ]);
    }

    function it_normalizes_the_whole_option_and_ignore_disabled_locales(
        AttributeOption $option,
        AbstractAttribute $attribute,
        AttributeOptionValue $valueEn,
        AttributeOptionValue $valueFr
    ) {
        $option->getCode()->willReturn('red');
        $option->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $option->getOptionValues()->willReturn([
            'en_US' => $valueEn,
            'fr_FR' => $valueFr,
        ]);
        $valueEn->getLocale()->willReturn('en_US');
        $valueEn->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');

        $this->normalize($option, null, ['locales' => ['en_US', 'de_DE']])->shouldReturn([
            'attribute' => 'color',
            'code' => 'red',
            'label' => ['en_US' => 'Red', 'de_DE' => '']
        ]);
    }

    function it_provides_all_locales_if_no_list_provided_in_context(
        AttributeOption $option,
        AbstractAttribute $attribute,
        AttributeOptionValue $valueEn,
        AttributeOptionValue $valueFr,
        AttributeOptionValue $valueDe
    ) {
        $option->getCode()->willReturn('red');
        $option->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $option->getOptionValues()->willReturn([
            'en_US' => $valueEn,
            'fr_FR' => $valueFr,
            'de_DE' => $valueDe
        ]);
        $valueEn->getLocale()->willReturn('en_US');
        $valueEn->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');
        $valueDe->getLocale()->willReturn('de_DE');
        $valueDe->getValue()->willReturn('');

        $this->normalize($option, null, ['locales' => []])->shouldReturn([
            'attribute' => 'color',
            'code' => 'red',
            'label' => ['en_US' => 'Red', 'fr_FR' => 'Rouge', 'de_DE' => '']
        ]);
    }
}
