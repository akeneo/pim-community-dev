<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionValueInterface;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_csv_normalization_of_attribute_option(AttributeOptionInterface $option)
    {
        $this->supportsNormalization($option, 'csv')->shouldBe(true);
    }

    function it_supports_flat_normalization_of_attribute_option(AttributeOptionInterface $option)
    {
        $this->supportsNormalization($option, 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_option_code_when_field_name_is_provided(AttributeOptionInterface $option)
    {
        $option->getCode()->willReturn('red');

        $this->normalize($option, null, ['field_name' => 'color'])->shouldReturn(['color' => 'red']);
    }

    function it_normalizes_the_whole_option(
        AttributeOptionInterface $option,
        AttributeInterface $attribute,
        AttributeOptionValueInterface $valueEn,
        AttributeOptionValueInterface $valueFr
    ) {
        $option->getCode()->willReturn('red');
        $option->getAttribute()->willReturn($attribute);
        $option->getSortOrder()->willReturn(1);
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
            'sort_order' => 1,
            'label-en_US' => 'Red',
            'label-fr_FR' => 'Rouge',
            'label-de_DE' => '',
        ]);
    }

    function it_normalizes_the_whole_option_and_ignore_disabled_locales(
        AttributeOptionInterface $option,
        AttributeInterface $attribute,
        AttributeOptionValueInterface $valueEn,
        AttributeOptionValueInterface $valueFr
    ) {
        $option->getCode()->willReturn('red');
        $option->getAttribute()->willReturn($attribute);
        $option->getSortOrder()->willReturn(1);
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
            'sort_order' => 1,
            'label-en_US' => 'Red',
            'label-de_DE' => '',
        ]);
    }

    function it_provides_all_locales_if_no_list_provided_in_context(
        AttributeOptionInterface $option,
        AttributeInterface $attribute,
        AttributeOptionValueInterface $valueEn,
        AttributeOptionValueInterface $valueFr,
        AttributeOptionValueInterface $valueDe
    ) {
        $option->getCode()->willReturn('red');
        $option->getAttribute()->willReturn($attribute);
        $option->getSortOrder()->willReturn(1);
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
            'sort_order' => 1,
            'label-en_US' => 'Red',
            'label-fr_FR' => 'Rouge',
            'label-de_DE' => '',
        ]);
    }
}
