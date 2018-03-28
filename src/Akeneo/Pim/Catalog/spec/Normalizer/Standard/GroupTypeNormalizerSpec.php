<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer;

class GroupTypeNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer)
    {
        $this->beConstructedWith($translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\GroupTypeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(GroupTypeInterface $groupType)
    {
        $this->supportsNormalization($groupType, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($groupType, 'xml')->shouldReturn(false);
        $this->supportsNormalization($groupType, 'json')->shouldReturn(false);
    }

    function it_normalizes_group_type($translationNormalizer, GroupTypeInterface $groupType)
    {
        $translationNormalizer->normalize($groupType, 'standard', [])->willReturn([]);

        $groupType->getCode()->willReturn('my_code');

        $this->normalize($groupType)->shouldReturn([
            'code'       => 'my_code',
            'labels' => []
        ]);
    }
}
