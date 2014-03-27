<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\TransformBundle\Normalizer\TranslationNormalizer;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_group(Group $group)
    {
        $this->supportsNormalization($family, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($family, 'json')->shouldBe(false);
        $this->supportsNormalization($family, 'xml')->shouldBe(false);
    }

    function it_normalizes_group(
        TranslationNormalizer $normalizer,
        Group $group
    ) {
        $group->getCode()->willReturn('mongo');
        $normalizer->normalize($group, 'mongodb_json', [])->willReturn(['label' => 'translations']);

        $this->normalize($group, 'mongodb_json', [])->shouldReturn([
            'code' => 'mongo',
            'label' => 'translations'
        ]);
    }
}
