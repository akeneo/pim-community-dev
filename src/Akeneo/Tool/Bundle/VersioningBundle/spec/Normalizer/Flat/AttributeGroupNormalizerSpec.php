<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Normalizer\Standard\AttributeGroupNormalizer;
use Prophecy\Argument;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(
        AttributeGroupNormalizer $attributeGroupNormalizerStandard,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->beConstructedWith($attributeGroupNormalizerStandard, $translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\AttributeGroupNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_group_normalization_into_flat(AttributeGroupInterface $attributeGroup)
    {
        $this->supportsNormalization($attributeGroup, 'flat')->shouldBe(true);
        $this->supportsNormalization($attributeGroup, 'csv')->shouldBe(false);
        $this->supportsNormalization($attributeGroup, 'json')->shouldBe(false);
        $this->supportsNormalization($attributeGroup, 'xml')->shouldBe(false);
    }

    function it_normalizes_attribute_group(
        AttributeGroupNormalizer $attributeGroupNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        AttributeGroupInterface $attributeGroup
    ) {
        $translationNormalizer->supportsNormalization(Argument::cetera())
            ->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera())
            ->willReturn([
                'label-en_US' => 'My attribute group',
                'label-fr_FR' => 'Mon group d\'attribut',
            ]);

        $attributeGroupNormalizerStandard->supportsNormalization($attributeGroup, 'standard')
            ->willReturn(true);
        $attributeGroupNormalizerStandard->normalize($attributeGroup, 'standard', [])
            ->willReturn(
                [
                    'code'       => 'code',
                    'sort_order' => 1,
                    'attributes' => ['price', 'size', 'type'],
                    'labels'     => [
                        'en_US' => 'My attribute group',
                        'fr_FR' => 'Mon group d\'attribut',
                    ],
                ]
            );

        $this->normalize($attributeGroup)->shouldReturn([
            'code'       => 'code',
            'sort_order' => 1,
            'attributes' => 'price,size,type',
            'label-en_US' => 'My attribute group',
            'label-fr_FR' => 'Mon group d\'attribut',
        ]);
    }
}
