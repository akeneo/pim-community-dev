<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($translationNormalizer, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\AttributeGroupNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(AttributeGroupInterface $attributeGroup)
    {
        $this->supportsNormalization($attributeGroup, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($attributeGroup, 'xml')->shouldReturn(false);
        $this->supportsNormalization($attributeGroup, 'json')->shouldReturn(false);
    }

    function it_normalizes_attribute_group(
        $translationNormalizer,
        $attributeRepository,
        AttributeGroupInterface $attributeGroup
    ) {
        $translationNormalizer->normalize($attributeGroup, 'standard', [])->willReturn([]);

        $attributeGroup->getCode()->willReturn('my_code');
        $attributeGroup->getSortOrder()->willReturn(1);
        $attributeRepository->getAttributeCodesByGroup($attributeGroup)->willReturn(['sku', 'color', 'ref_data_color']);

        $this->normalize($attributeGroup)->shouldReturn([
            'code'   => 'my_code',
            'sort_order' => 1,
            'attributes' => [
                'sku', 'color', 'ref_data_color'
            ],
            'labels' => []
        ]);
    }
}
