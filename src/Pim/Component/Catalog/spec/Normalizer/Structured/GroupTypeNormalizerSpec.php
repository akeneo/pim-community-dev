<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Normalizer\Structured\GroupTypeNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupTypeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $nomalizer)
    {
        $this->beConstructedWith($nomalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GroupTypeNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_group_type_normalization_into_csv(GroupTypeInterface $currency)
    {
        $this->supportsNormalization($currency, 'csv')->shouldBe(false);
        $this->supportsNormalization($currency, 'flat')->shouldBe(false);
        $this->supportsNormalization($currency, 'json')->shouldBe(true);
        $this->supportsNormalization($currency, 'xml')->shouldBe(true);
    }

    function it_normalizes_group_type($nomalizer, GroupTypeInterface $groupType)
    {
        $nomalizer->normalize(Argument::cetera())->willReturn([]);
        $groupType->getCode()->willReturn('RELATED');
        $groupType->isVariant()->willReturn(false);
        $this->normalize($groupType)->shouldReturn(
            [
                'code'      => 'RELATED',
                'is_variant' => false,
            ]
        );
    }
}
