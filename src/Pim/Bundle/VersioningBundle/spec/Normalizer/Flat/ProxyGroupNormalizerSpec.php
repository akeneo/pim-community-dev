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
        GroupNormalizer $groupNormalizerFlat,
        VariantGroupNormalizer $variantGroupNormalizerFlat
    ) {
        $this->beConstructedWith(
            $groupNormalizerFlat,
            $variantGroupNormalizerFlat
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
        VariantGroupNormalizer $variantGroupNormalizerFlat,
        GroupInterface $group,
        GroupTypeInterface $groupType
    ) {
        $group->getType()->willReturn($groupType);
        $groupType->isVariant()->willReturn(false);

        $groupNormalizerFlat->normalize($group, 'flat', [])->shouldBeCalled();
        $variantGroupNormalizerFlat->normalize($group, 'flat', [])->shouldNotBeCalled();

        $this->normalize($group, 'flat', []);
    }

    function it_normalizes_variant_groups_into_flat(
        GroupNormalizer $groupNormalizerFlat,
        VariantGroupNormalizer $variantGroupNormalizerFlat,
        GroupInterface $group,
        GroupTypeInterface $groupType
    ) {
        $group->getType()->willReturn($groupType);
        $groupType->isVariant()->willReturn(true);

        $variantGroupNormalizerFlat->normalize($group, 'flat', [])->shouldBeCalled();
        $groupNormalizerFlat->normalize($group, 'flat', [])->shouldNotBeCalled();

        $this->normalize($group, 'flat', []);
    }
}

