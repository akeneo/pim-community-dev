<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\Normalizer\Versioning\AttributeGroupNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
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
        $this->shouldHaveType(AttributeGroupNormalizer::class);
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
