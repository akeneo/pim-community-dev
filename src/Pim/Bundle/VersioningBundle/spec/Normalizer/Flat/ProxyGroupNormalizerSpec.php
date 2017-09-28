<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\GroupNormalizer;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\VariantGroupNormalizer;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;

class ProxyGroupNormalizerSpec extends ObjectBehavior
{
    function let(
        GroupNormalizer $groupNormalizerFlat
    ) {
        $this->beConstructedWith(
            $groupNormalizerFlat
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\ProxyGroupNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization(GroupInterface $group)
    {
        $this->supportsNormalization($group, 'flat')->shouldReturn(true);
        $this->supportsNormalization($group, 'csv')->shouldReturn(false);
    }

    function it_normalizes_groups_into_flat(
        GroupNormalizer $groupNormalizerFlat,
        GroupInterface $group,
        GroupTypeInterface $groupType
    ) {
        $group->getType()->willReturn($groupType);

        $groupNormalizerFlat->normalize($group, 'flat', [])->shouldBeCalled();

        $this->normalize($group, 'flat', []);
    }
}

