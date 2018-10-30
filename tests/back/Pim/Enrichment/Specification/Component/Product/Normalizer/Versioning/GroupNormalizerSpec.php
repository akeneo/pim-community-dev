<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\GroupNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(
        GroupNormalizer $groupNormalizerStandard,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->beConstructedWith($groupNormalizerStandard, $translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GroupNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_only_supports_flat_normalization_of_group(GroupInterface $group)
    {
        $this->supportsNormalization($group, 'flat')->shouldReturn(true);
        $this->supportsNormalization($group, 'csv')->shouldReturn(false);
        $this->supportsNormalization($group, 'flat')->shouldReturn(true);
        $this->supportsNormalization($group, 'json')->shouldReturn(false);
    }

    function it_normalizes_a_group(
        GroupNormalizer $groupNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        GroupInterface $group
    ) {
        $translationNormalizer->supportsNormalization(Argument::cetera())->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera(), 'flat', [])->willReturn([]);

        $groupNormalizerStandard->supportsNormalization($group, 'standard')->willReturn(true);
        $groupNormalizerStandard->normalize($group, 'standard', [])->willReturn(
            [
                'code'   => 'mugs',
                'type'   => 'RELATED',
                'labels' => [],
            ]
        );

        $this->normalize($group, 'flat', [])->shouldReturn(
            [
                'code'   => 'mugs',
                'type'   => 'RELATED',
            ]
        );
    }
}

