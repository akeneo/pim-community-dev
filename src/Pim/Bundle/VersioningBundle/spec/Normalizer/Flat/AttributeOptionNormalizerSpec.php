<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Normalizer\Standard\AttributeOptionNormalizer;
use Prophecy\Argument;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function let(
        AttributeOptionNormalizer $attributeOptionNormalizerStandard,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->beConstructedWith($attributeOptionNormalizerStandard, $translationNormalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization_of_attribute_option(AttributeOptionInterface $option)
    {
        $this->supportsNormalization($option, 'flat')->shouldBe(true);
        $this->supportsNormalization($option, 'csv')->shouldBe(false);
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_the_whole_option(
        AttributeOptionNormalizer $attributeOptionNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        AttributeOptionInterface $attributeOption
    ) {
        $translationNormalizer->supportsNormalization(Argument::cetera())
            ->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera())->willReturn(
            [
                'label-en_US' => 'Red',
                'label-fr_FR' => 'Rouge',
                'label-de_DE' => '',
            ]
        );

        $attributeOptionNormalizerStandard->supportsNormalization($attributeOption, 'standard')
            ->willReturn(true);
        $attributeOptionNormalizerStandard->normalize($attributeOption, 'standard', [])->willReturn([
            'code' => 'red',
            'attribute' => 'color',
            'sort_order' => 1,
            'labels' => [
                'en_US' => 'Red',
                'fr_FR' => 'Rouge',
                'de_DE' => ''
            ]
        ]);

        $this->normalize($attributeOption, null, [])->shouldReturn([
            'code' => 'red',
            'attribute' => 'color',
            'sort_order' => 1,
            'label-en_US' => 'Red',
            'label-fr_FR' => 'Rouge',
            'label-de_DE' => '',
        ]);
    }
}
