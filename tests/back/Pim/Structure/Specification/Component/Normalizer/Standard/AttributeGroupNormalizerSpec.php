<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeGroupNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($translationNormalizer, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupNormalizer::class);
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
