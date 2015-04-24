<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\TransformBundle\Normalizer\Structured\TranslationNormalizer;
use Prophecy\Argument;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $normalizer, AttributeGroupInterface $group)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Flat\AttributeGroupNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_group_normalization_into_csv($group)
    {
        $this->supportsNormalization($group, 'csv')->shouldBe(true);
        $this->supportsNormalization($group, 'json')->shouldBe(false);
        $this->supportsNormalization($group, 'xml')->shouldBe(false);
    }

    function it_normalizes_attribute_group(
        $normalizer,
        $group,
        AttributeInterface $type,
        AttributeInterface $size,
        AttributeInterface $price
    ) {
        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $type->getCode()->willReturn('type');
        $size->getCode()->willReturn('size');
        $price->getCode()->willReturn('price');
        $group->getAttributes()->willReturn([$type, $size, $price]);
        $group->getCode()->willReturn('code');
        $group->getSortOrder()->willReturn(1);
        $this->normalize($group)->shouldReturn([
            'code'       => 'code',
            'sortOrder'  => 1,
            'attributes' => 'type,size,price'
        ]);
    }
}
