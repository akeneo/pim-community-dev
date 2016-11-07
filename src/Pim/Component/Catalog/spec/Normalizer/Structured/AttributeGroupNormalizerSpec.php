<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Normalizer\Structured\TranslationNormalizer;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $normalizer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($normalizer, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Structured\AttributeGroupNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_group_normalization_into_json_and_xml(AttributeGroupInterface $group)
    {
        $this->supportsNormalization($group, 'json')->shouldBe(true);
        $this->supportsNormalization($group, 'xml')->shouldBe(true);
        $this->supportsNormalization($group, 'csv')->shouldBe(false);
    }

    function it_normalizes_attribute_group(
        $normalizer,
        $attributeRepository,
        AttributeGroupInterface $group
    ) {
        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $attributeRepository->getAttributeCodesByGroup($group)->willReturn(['type', 'size', 'price']);
        $group->getCode()->willReturn('code');
        $group->getSortOrder()->willReturn(1);

        $this->normalize($group)->shouldReturn([
            'code'       => 'code',
            'sort_order' => 1,
            'attributes' => ['type', 'size', 'price']
        ]);
    }
}
