<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer;

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

    function it_supports_normalization_in_mongodb_json_of_group(GroupInterface $group)
    {
        $this->supportsNormalization($group, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($group, 'json')->shouldBe(false);
        $this->supportsNormalization($group, 'xml')->shouldBe(false);
    }

    function it_normalizes_group(
        $normalizer,
        GroupInterface $group
    ) {
        $group->getCode()->willReturn('mongo');
        $normalizer->normalize($group, 'mongodb_json', [])->willReturn(['label' => 'translations']);

        $this->normalize($group, 'mongodb_json', [])->shouldReturn([
            'code' => 'mongo',
            'label' => 'translations'
        ]);
    }
}
